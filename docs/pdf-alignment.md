# Final Project PDF alignment

Reference: Final Project.docx.pdf, ITELEC 4100, first semester AY 2026?2027. The restaurant domain and requested account behavior are retained; the PDF's product/category names are examples.

| Requirement | Implementation |
| --- | --- |
| MVC and validation | Resource controllers in app/Http/Controllers; reusable restaurant logic in app/Services/RestaurantRecords.php; React presentation in resources/js |
| Migrations, keys, timestamps | database/migrations; new audit timestamp migration preserves existing records; user ownership foreign keys on orders and reservations |
| Eloquent relationships and fillable | User hasMany orders/reservations; Order belongsTo user/menuItem/table; Reservation belongsTo user/table; MenuItem and RestaurantTable inverse relations; ownership is never mass assignable |
| Resource CRUD methods | OrderController, ReservationController, InventoryController inherit index/create/store/show/edit/update/destroy from RestaurantResourceController; page endpoints render Inertia; API endpoints return JSON |
| Modular named routes | routes/orders.php, reservations.php, inventory.php, otp.php; web.php contains dashboard/lookup routes |
| React/Inertia and Vite | app.jsx uses createInertiaApp and resolvePageComponent; app.blade.php contains Vite/Inertia directives; shared auth props via HandleInertiaRequests |
| Fortify authentication | Native Fortify login, registration, logout and password reset routes; VerifyLogin pipeline handles unfinished email verification; RegisterResponse starts registration OTP |
| Password reset | ForgotPassword and ResetPassword React pages; Laravel password broker emails expiring, single-use tokens; ResetUserPassword validates and hashes the new password |
| Spatie authorization | HasRoles, permission migrations, RolesAndPermissionsSeeder; auth/can middleware plus server-side ownership/role checks |
| CSRF | Laravel web middleware, Inertia forms and X-XSRF-TOKEN on JSON mutations |
| Conditional UI | Customer has own orders/reservations; staff retains inventory access; Admin sees all records |

## Deliberate domain choices

User deletion sets order/reservation ownership to null rather than deleting business history. Menu/table foreign keys protect historical records; permission pivot tables use cascading foreign keys. These constraints are intentional restaurant-domain equivalents of the PDF's cascade example.

Existing legacy records are preserved. Added timestamps remain null when the original time is unknown. The workspace directory and RestroFlow branding are retained rather than renaming a working installation to the PDF's example project name FinalTermLaravel.

Registration requires OTP; subsequent verified logins do not. Admin remains exempt from OTP. Login accepts email or assigned username. Back/Forward revalidates the server session.

## Validation and demonstration

Run php artisan test, npm run build, and npx playwright test. Use real SMTP credentials for actual reset/OTP delivery; automated tests fake mail and browser tests use the log transport. No live email delivery claim is made.

Demonstrate registration + OTP, logout/login without OTP, password reset, customer order/reservation privacy, admin CRUD, and Back after logout. Show /orders, /orders/create, /orders/{id}, /orders/{id}/edit with the appropriate account to demonstrate resource pages; API routes remain under /api.
