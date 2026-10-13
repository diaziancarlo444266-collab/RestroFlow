$(document).ready(function () {
    const apiUrl = 'api/index.php';

    // Local arrays make it easy to find a record when the Edit button is clicked.
    let orders = [];
    let reservations = [];
    let inventory = [];
    let lookups = { menu: [], tables: [] };

    const orderModal = new bootstrap.Modal('#orderModal');
    const reservationModal = new bootstrap.Modal('#reservationModal');
    const inventoryModal = new bootstrap.Modal('#inventoryModal');

    // Sends an AJAX request and returns JSON from api/index.php.
    function api(module, action, data = {}, method = 'GET') {
        const requestData = typeof data === 'string'
            ? data + '&' + $.param({ module: module, action: action })
            : Object.assign({ module: module, action: action }, data);

        return $.ajax({
            url: apiUrl,
            type: method,
            dataType: 'json',
            data: requestData
        });
    }

    // Prevents database text from being treated as HTML code.
    function clean(text) {
        return $('<div>').text(text ?? '').html();
    }

    function showMessage(message, error = false) {
        const color = error ? 'danger' : 'success';
        $('#messageBox').html(`<div class="alert alert-${color} alert-dismissible fade show">
            ${clean(message)}<button class="btn-close" data-bs-dismiss="alert"></button></div>`);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showError(xhr) {
        showMessage(xhr.responseJSON?.message || 'An error occurred.', true);
    }

    function peso(amount) {
        return '₱' + Number(amount).toFixed(2);
    }

    // Changes the visible section without reloading the whole page.
    $('.nav-link[data-page]').click(function () {
        const page = $(this).data('page');
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        $('.app-page').addClass('d-none');
        $('#' + page + 'Page').removeClass('d-none');
        $('.navbar-collapse').removeClass('show');

        if (page === 'dashboard') loadDashboard();
        if (page === 'orders') loadOrders();
        if (page === 'reservations') loadReservations();
        if (page === 'inventory') loadInventory();
    });

    // -------------------- DASHBOARD --------------------
    function loadDashboard() {
        api('dashboard', 'list').done(function (response) {
            $('#orderCount').text(response.data.orders);
            $('#reservationCount').text(response.data.reservations);
            $('#inventoryCount').text(response.data.inventory);
            $('#lowStockCount').text(response.data.low_stock);
        }).fail(showError);

        // Only the five newest records are shown on the dashboard.
        api('orders', 'list').done(function (response) {
            const rows = response.data.slice(0, 5).map(function (order) {
                return `<tr><td>${clean(order.customer_name)}</td><td>${clean(order.menu_name)}</td>
                    <td>${clean(order.status)}</td><td>${peso(order.total_amount)}</td></tr>`;
            }).join('');
            $('#dashboardOrders').html(rows || '<tr><td colspan="4" class="empty-row">No orders.</td></tr>');
        });

        api('reservations', 'list').done(function (response) {
            const rows = response.data.slice(0, 5).map(function (item) {
                return `<tr><td>${clean(item.customer_name)}</td><td>${clean(item.table_number)}</td>
                    <td>${clean(item.reservation_date)}</td></tr>`;
            }).join('');
            $('#dashboardReservations').html(rows || '<tr><td colspan="3" class="empty-row">No reservations.</td></tr>');
        });
    }

    // Loads menu items and tables for the form dropdowns.
    function loadLookups() {
        api('lookups', 'list').done(function (response) {
            lookups = response.data;

            const menuOptions = '<option value="">Select menu item</option>' + lookups.menu.map(function (item) {
                return `<option value="${item.id}">${clean(item.name)} - ${peso(item.price)}</option>`;
            }).join('');

            const tableOptions = '<option value="">No table / Takeout</option>' + lookups.tables.map(function (table) {
                return `<option value="${table.id}">${clean(table.table_number)} (${table.capacity} seats)</option>`;
            }).join('');

            $('#orderMenu').html(menuOptions);
            $('#orderTable').html(tableOptions);
            $('#reservationTable').html(tableOptions.replace('No table / Takeout', 'Select table'));
        }).fail(showError);
    }

    // -------------------- ORDERS CRUD --------------------
    function loadOrders(search = '') {
        api('orders', 'list', { search: search }).done(function (response) {
            orders = response.data;
            let rows = '';

            orders.forEach(function (order) {
                rows += `<tr>
                    <td>${clean(order.customer_name)}</td>
                    <td>${clean(order.menu_name)}</td>
                    <td>${clean(order.table_number || 'Takeout')}</td>
                    <td>${order.quantity}</td>
                    <td>${peso(order.total_amount)}</td>
                    <td><span class="badge text-bg-secondary">${clean(order.status)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary action-button edit-order" data-id="${order.id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger delete-order" data-id="${order.id}"><i class="bi bi-trash"></i></button>
                    </td></tr>`;
            });

            $('#ordersBody').html(rows || '<tr><td colspan="7" class="empty-row">No orders found.</td></tr>');
        }).fail(showError);
    }

    $('#addOrderBtn').click(function () {
        $('#orderForm')[0].reset();
        $('#orderForm [name=id]').val('');
        orderModal.show();
    });

    // The dashboard button opens the same simple order form.
    $('#dashboardAddOrder').click(function () {
        $('#addOrderBtn').trigger('click');
    });

    $(document).on('click', '.edit-order', function () {
        const order = orders.find(item => item.id == $(this).data('id'));
        Object.keys(order).forEach(key => $(`#orderForm [name="${key}"]`).val(order[key]));
        orderModal.show();
    });

    $('#orderForm').submit(function (event) {
        event.preventDefault();
        api('orders', 'save', $(this).serialize(), 'POST').done(function (response) {
            orderModal.hide();
            showMessage(response.message);
            loadOrders();
            loadDashboard();
        }).fail(showError);
    });

    $(document).on('click', '.delete-order', function () {
        if (!confirm('Delete this order?')) return;
        api('orders', 'delete', { id: $(this).data('id') }, 'POST').done(function (response) {
            showMessage(response.message); loadOrders(); loadDashboard();
        }).fail(showError);
    });

    $('#orderSearch').on('input', function () { loadOrders(this.value); });

    // -------------------- RESERVATIONS CRUD --------------------
    function loadReservations(search = '') {
        api('reservations', 'list', { search: search }).done(function (response) {
            reservations = response.data;
            let rows = '';

            reservations.forEach(function (reservation) {
                rows += `<tr>
                    <td>${clean(reservation.customer_name)}</td><td>${clean(reservation.phone)}</td>
                    <td>${clean(reservation.table_number)}</td>
                    <td>${clean(reservation.reservation_date)} ${clean(reservation.reservation_time.slice(0, 5))}</td>
                    <td>${reservation.guests}</td><td><span class="badge text-bg-secondary">${clean(reservation.status)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary action-button edit-reservation" data-id="${reservation.id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger delete-reservation" data-id="${reservation.id}"><i class="bi bi-trash"></i></button>
                    </td></tr>`;
            });

            $('#reservationsBody').html(rows || '<tr><td colspan="7" class="empty-row">No reservations found.</td></tr>');
        }).fail(showError);
    }

    $('#addReservationBtn').click(function () {
        $('#reservationForm')[0].reset();
        $('#reservationForm [name=id]').val('');
        $('#reservationForm [name=reservation_date]').val(new Date().toISOString().slice(0, 10));
        reservationModal.show();
    });

    $(document).on('click', '.edit-reservation', function () {
        const reservation = reservations.find(item => item.id == $(this).data('id'));
        Object.keys(reservation).forEach(key => $(`#reservationForm [name="${key}"]`).val(reservation[key]));
        reservationModal.show();
    });

    $('#reservationForm').submit(function (event) {
        event.preventDefault();
        api('reservations', 'save', $(this).serialize(), 'POST').done(function (response) {
            reservationModal.hide(); showMessage(response.message); loadReservations(); loadDashboard();
        }).fail(showError);
    });

    $(document).on('click', '.delete-reservation', function () {
        if (!confirm('Delete this reservation?')) return;
        api('reservations', 'delete', { id: $(this).data('id') }, 'POST').done(function (response) {
            showMessage(response.message); loadReservations(); loadDashboard();
        }).fail(showError);
    });

    $('#reservationSearch').on('input', function () { loadReservations(this.value); });

    // -------------------- INVENTORY CRUD --------------------
    function loadInventory(search = '') {
        api('inventory', 'list', { search: search }).done(function (response) {
            inventory = response.data;
            let rows = '';

            inventory.forEach(function (item) {
                const lowStock = Number(item.quantity) <= Number(item.reorder_level);
                rows += `<tr>
                    <td>${clean(item.item_name)}</td><td>${item.quantity}</td><td>${clean(item.unit)}</td>
                    <td>${item.reorder_level}</td>
                    <td class="${lowStock ? 'low-stock' : ''}">${lowStock ? 'Low Stock' : 'Available'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary action-button edit-inventory" data-id="${item.id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger delete-inventory" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                    </td></tr>`;
            });

            $('#inventoryBody').html(rows || '<tr><td colspan="6" class="empty-row">No inventory items found.</td></tr>');
        }).fail(showError);
    }

    $('#addInventoryBtn').click(function () {
        $('#inventoryForm')[0].reset();
        $('#inventoryForm [name=id]').val('');
        inventoryModal.show();
    });

    $(document).on('click', '.edit-inventory', function () {
        const item = inventory.find(record => record.id == $(this).data('id'));
        Object.keys(item).forEach(key => $(`#inventoryForm [name="${key}"]`).val(item[key]));
        inventoryModal.show();
    });

    $('#inventoryForm').submit(function (event) {
        event.preventDefault();
        api('inventory', 'save', $(this).serialize(), 'POST').done(function (response) {
            inventoryModal.hide(); showMessage(response.message); loadInventory(); loadDashboard();
        }).fail(showError);
    });

    $(document).on('click', '.delete-inventory', function () {
        if (!confirm('Delete this inventory item?')) return;
        api('inventory', 'delete', { id: $(this).data('id') }, 'POST').done(function (response) {
            showMessage(response.message); loadInventory(); loadDashboard();
        }).fail(showError);
    });

    $('#inventorySearch').on('input', function () { loadInventory(this.value); });

    // Initial page data.
    loadDashboard();
    loadLookups();
});
