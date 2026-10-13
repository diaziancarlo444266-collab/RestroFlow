# Restaurant Management System

A simple student project for ITELEC 4100. It uses plain object-oriented PHP, MySQL, Bootstrap, jQuery, and AJAX. No framework is used.

## How to run

1. Start Apache and MySQL in XAMPP.
2. Open `http://localhost/1stTermProj/`.
3. The database is already saved in phpMyAdmin as `restroflow_db`.

## Main modules

1. **Orders** — Add, display, search, edit, and delete an order.
2. **Reservations** — Add, display, search, edit, and delete a reservation.
3. **Inventory** — Add, display, search, edit, and delete an inventory item.

## PDF requirements followed

- PHP uses an object-oriented approach.
- All three main modules have CRUD operations.
- MySQL is used as the database.
- Database tables have foreign-key relationships.
- Bootstrap is used for the design.
- jQuery AJAX updates records without reloading the page.

## Simple program flow

`Form → jQuery AJAX → api/index.php → RestaurantManager class → MySQL → JSON response`

## Files to explain

- `index.php` contains the pages, tables, and Bootstrap forms.
- `assets/js/app.js` sends AJAX requests and displays the returned records.
- `api/index.php` receives the request and calls the correct model method.
- `app/Core/Database.php` creates the PDO connection.
- `app/Models/RestaurantManager.php` contains all CRUD SQL queries.
- `config.php` contains the database settings.

Short comments are included in the source files above the important sections.

## OOP explanation

- **Class:** `Database` and `RestaurantManager` are PHP classes.
- **Object:** The API creates objects using `new Database()` and `new RestaurantManager()`.
- **Encapsulation:** Database code is stored inside classes instead of the HTML page.
- **Methods:** Operations use methods such as `getOrders()`, `saveOrder()`, and `deleteOrder()`.

## Database relationships

- `orders.menu_item_id` connects an order to `menu_items.id`.
- `orders.table_id` connects an order to `restaurant_tables.id`.
- `reservations.table_id` connects a reservation to `restaurant_tables.id`.

## Suggested presentation

> This is our Restaurant Management System. It has three main modules: Orders, Reservations, and Inventory. Each module supports CRUD operations. The interface uses Bootstrap, while jQuery AJAX sends data to our PHP API without reloading the page. The backend uses object-oriented PHP. The Database class handles the PDO connection, and the RestaurantManager class contains the CRUD methods. MySQL foreign keys connect orders to menu items and tables, and reservations to restaurant tables.

For the demo:

1. Add and edit an order.
2. Add a reservation and select a restaurant table.
3. Change an inventory quantity to show the low-stock label.
4. Search and delete a test record.
