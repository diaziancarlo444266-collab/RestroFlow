# Account access

- Admin: all orders, reservations, dashboard totals, and inventory, with management actions.
- Customer: place orders, reserve tables, and view only orders and reservations linked to their account. No inventory access or record editing/deletion. Reservations start as Pending; Admin manages confirmation.
- Existing Viewer accounts follow the same customer restrictions.
- Staff: their own orders and existing inventory management; no access to other accounts' orders or reservations.

Public registration assigns Customer. Order and reservation ownership is set from the signed-in account on the server. Customer names and Pending status are also set on the server; totals use stored menu prices.

Existing orders and reservations have no account owner and remain visible only to Admin. Names are not used to infer ownership.

For another installation, run `php artisan migrate` and `php artisan db:seed --class=RolesAndPermissionsSeeder`. The server administrator can assign Customer with `php artisan app:set-role person@example.com Customer`.
