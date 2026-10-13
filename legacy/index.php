<?php
// The config is loaded here only to display the application name.
$config = require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Simple navigation for the four pages -->
    <nav class="navbar navbar-dark navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#"><span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span><span>RestroFlow<small>Restaurant Manager</small></span></a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <div class="navbar-nav ms-auto">
                    <span class="nav-label">Workspace</span>
                    <button class="nav-link active" data-page="dashboard">Dashboard</button>
                    <button class="nav-link" data-page="orders">Orders</button>
                    <button class="nav-link" data-page="reservations">Reservations</button>
                    <button class="nav-link" data-page="inventory">Inventory</button>
                </div>
                <div class="system-status"><span></span><div><strong>System ready</strong><small>PHP + MySQL</small></div></div>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <div id="messageBox"></div>

        <!-- Dashboard page -->
        <section id="dashboardPage" class="app-page">
            <div class="welcome-banner">
                <div>
                    <small>TODAY AT A GLANCE</small>
                    <h1>Everything your restaurant needs.</h1>
                    <p>Track orders, guests, and supplies in one simple workspace.</p>
                </div>
                <button class="btn banner-button" id="dashboardAddOrder"><i class="bi bi-plus-lg"></i> New Order</button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="summary-card"><i class="bi bi-receipt"></i><span>Total Orders</span><strong id="orderCount">0</strong></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="summary-card"><i class="bi bi-calendar-check"></i><span>Reservations</span><strong id="reservationCount">0</strong></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="summary-card"><i class="bi bi-box-seam"></i><span>Inventory Items</span><strong id="inventoryCount">0</strong></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="summary-card warning"><i class="bi bi-exclamation-triangle"></i><span>Low Stock</span><strong id="lowStockCount">0</strong></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="card simple-card h-100"><div class="card-body">
                        <h5>Recent Orders</h5>
                        <div class="table-responsive"><table class="table"><thead><tr><th>Customer</th><th>Order</th><th>Status</th><th>Total</th></tr></thead><tbody id="dashboardOrders"></tbody></table></div>
                    </div></div>
                </div>
                <div class="col-lg-5">
                    <div class="card simple-card h-100"><div class="card-body">
                        <h5>Recent Reservations</h5>
                        <div class="table-responsive"><table class="table"><thead><tr><th>Customer</th><th>Table</th><th>Date</th></tr></thead><tbody id="dashboardReservations"></tbody></table></div>
                    </div></div>
                </div>
            </div>
        </section>

        <!-- Orders CRUD page -->
        <section id="ordersPage" class="app-page d-none">
            <div class="page-heading d-flex justify-content-between align-items-center">
                <div><h2>Orders</h2><p>Add, view, edit, and delete customer orders.</p></div>
                <button class="btn btn-success" id="addOrderBtn"><i class="bi bi-plus-lg"></i> Add Order</button>
            </div>
            <div class="card simple-card">
                <div class="card-body">
                    <input class="form-control search-input mb-3" id="orderSearch" placeholder="Search customer or menu item">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Customer</th><th>Menu Item</th><th>Table</th><th>Qty.</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody id="ordersBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reservations CRUD page -->
        <section id="reservationsPage" class="app-page d-none">
            <div class="page-heading d-flex justify-content-between align-items-center">
                <div><h2>Reservations</h2><p>Manage customer table reservations.</p></div>
                <button class="btn btn-success" id="addReservationBtn"><i class="bi bi-plus-lg"></i> Add Reservation</button>
            </div>
            <div class="card simple-card">
                <div class="card-body">
                    <input class="form-control search-input mb-3" id="reservationSearch" placeholder="Search customer or phone">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Customer</th><th>Phone</th><th>Table</th><th>Date & Time</th><th>Guests</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody id="reservationsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Inventory CRUD page -->
        <section id="inventoryPage" class="app-page d-none">
            <div class="page-heading d-flex justify-content-between align-items-center">
                <div><h2>Inventory</h2><p>Track the quantity of restaurant supplies.</p></div>
                <button class="btn btn-success" id="addInventoryBtn"><i class="bi bi-plus-lg"></i> Add Item</button>
            </div>
            <div class="card simple-card">
                <div class="card-body">
                    <input class="form-control search-input mb-3" id="inventorySearch" placeholder="Search item name">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Item Name</th><th>Quantity</th><th>Unit</th><th>Reorder Level</th><th>Stock Status</th><th>Action</th></tr></thead>
                            <tbody id="inventoryBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Order form modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form id="orderForm">
                <div class="modal-header"><h5 class="modal-title">Order Form</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="mb-3"><label class="form-label">Customer Name</label><input class="form-control" name="customer_name" required></div>
                    <div class="mb-3"><label class="form-label">Menu Item</label><select class="form-select" name="menu_item_id" id="orderMenu" required></select></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Table</label><select class="form-select" name="table_id" id="orderTable"></select></div>
                        <div class="col-6 mb-3"><label class="form-label">Quantity</label><input class="form-control" name="quantity" type="number" min="1" value="1" required></div>
                    </div>
                    <div><label class="form-label">Status</label><select class="form-select" name="status"><option>Pending</option><option>Preparing</option><option>Completed</option><option>Cancelled</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success">Save Order</button></div>
            </form>
        </div></div>
    </div>

    <!-- Reservation form modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form id="reservationForm">
                <div class="modal-header"><h5 class="modal-title">Reservation Form</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="mb-3"><label class="form-label">Customer Name</label><input class="form-control" name="customer_name" required></div>
                    <div class="mb-3"><label class="form-label">Phone Number</label><input class="form-control" name="phone" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Table</label><select class="form-select" name="table_id" id="reservationTable" required></select></div>
                        <div class="col-6 mb-3"><label class="form-label">Guests</label><input class="form-control" name="guests" type="number" min="1" value="1" required></div>
                        <div class="col-6 mb-3"><label class="form-label">Date</label><input class="form-control" name="reservation_date" type="date" required></div>
                        <div class="col-6 mb-3"><label class="form-label">Time</label><input class="form-control" name="reservation_time" type="time" required></div>
                    </div>
                    <div><label class="form-label">Status</label><select class="form-select" name="status"><option>Pending</option><option>Confirmed</option><option>Completed</option><option>Cancelled</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success">Save Reservation</button></div>
            </form>
        </div></div>
    </div>

    <!-- Inventory form modal -->
    <div class="modal fade" id="inventoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form id="inventoryForm">
                <div class="modal-header"><h5 class="modal-title">Inventory Form</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="mb-3"><label class="form-label">Item Name</label><input class="form-control" name="item_name" required></div>
                    <div class="row">
                        <div class="col-4 mb-3"><label class="form-label">Quantity</label><input class="form-control" name="quantity" type="number" min="0" step="0.01" required></div>
                        <div class="col-4 mb-3"><label class="form-label">Unit</label><input class="form-control" name="unit" placeholder="kg / pcs" required></div>
                        <div class="col-4 mb-3"><label class="form-label">Reorder At</label><input class="form-control" name="reorder_level" type="number" min="0" step="0.01" required></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success">Save Item</button></div>
            </form>
        </div></div>
    </div>

    <footer class="text-center py-4 text-muted"><small>Restaurant Management System — ITELEC 4100 Project</small></footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
