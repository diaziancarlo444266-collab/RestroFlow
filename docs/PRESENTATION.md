# RestroFlow presentation guide

Use docs/pdf-alignment.md as the requirement-to-code checklist. This is a presentation script, not a submitted recording.

1. Setup: run php artisan migrate --seed and npm run build; start Laravel and MySQL. Configure real SMTP for email demonstrations, or use the local log mailer. Never display .env credentials.
2. Registration: create a Customer, enter the email OTP, then sign out and sign in again without another code. Show Forgot password and complete a reset using the emailed link.
3. Customer: place an order and reserve a table. Show My Orders/My Reservations and explain that server queries scope records to user_id. New reservations are Pending. Customers cannot edit status or read another account's records.
4. Admin: sign in with the configured admin account. Show all orders/reservations and inventory CRUD. Staff retains inventory create/update but cannot delete. Existing Viewer accounts behave as customers.
5. Architecture: show modular routes, the three resource controllers and their shared base, RestaurantRecords, and User/Order/Reservation/MenuItem/RestaurantTable relationships. Show the audit timestamp migration; existing records remain intact.
6. Authentication: show FortifyServiceProvider, VerifyLogin, RegisterResponse, EmailOtp, and ResetUserPassword. Native Fortify controllers own authentication endpoints; OTP is a registration customization. Show Spatie HasRoles, role seeding, auth/can middleware, and ownership checks.
7. Frontend: show app.blade.php, app.jsx with resolvePageComponent, reusable RecordForm/DataTable, and the Inertia resource pages. Demonstrate Back after logout and the test results.

Role changes are server-administrator operations: php artisan app:set-role email@example.com Admin (or Staff/Customer/Viewer). Public registration never accepts an elevated role.
