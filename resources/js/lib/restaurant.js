export const money = value => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
export const titles = { dashboard: 'Dashboard', orders: 'Orders', reservations: 'Reservations', inventory: 'Inventory' };
export const singular = { orders: 'Order', reservations: 'Reservation', inventory: 'Item' };
export const columns = {
    orders: [['customer_name', 'Customer'], ['menu_name', 'Menu Item'], ['table_number', 'Table'], ['quantity', 'Qty.'], ['total_amount', 'Total'], ['status', 'Status']],
    reservations: [['customer_name', 'Customer'], ['phone', 'Phone'], ['table_number', 'Table'], ['reservation_date', 'Date'], ['reservation_time', 'Time'], ['guests', 'Guests'], ['status', 'Status']],
    inventory: [['item_name', 'Item Name'], ['quantity', 'Quantity'], ['unit', 'Unit'], ['reorder_level', 'Reorder Level'], ['stock', 'Stock Status']],
};
