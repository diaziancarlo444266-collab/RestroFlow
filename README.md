# RestroFlow — Laravel + React

Restaurant management project with Laravel 12, React 19, Inertia 3, Fortify, Spatie Roles and Permission, Vite, Bootstrap, and MySQL. Inertia delivers React pages and authentication forms; restaurant CRUD uses session-authenticated JSON endpoints through fetch.

## Run with your existing MySQL database

1. Start MySQL in XAMPP.
2. Check `.env`: the project uses `restroflow_db` on `127.0.0.1:3306`, username `root`, and a blank password.
3. Run in the project directory:

```powershell
php artisan migrate --seed
npm run build
php artisan serve
```

4. Open http://127.0.0.1:8000.

5. Register an account. New accounts have Viewer access. Promote your registered account from the project terminal:

```powershell
php artisan app:set-role your-email@example.com Admin
```

For the presentation, register separate Staff and Viewer accounts. Use `php artisan app:set-role staff@example.test Staff` for Staff access. Reload after changing a role. No default administrator password is shipped.

| Role | Read | Create | Update | Delete |
| --- | --- | --- | --- | --- |
| Admin | Yes | Yes | Yes | Yes |
| Staff | Yes | Yes | Yes | No |
| Viewer | Yes | No | No | No |

All restaurant routes require authentication and server-side permissions. Users without a role cannot access restaurant data. The role command requires server terminal access; there is no public role-assignment endpoint.

Dependencies and the application key have already been installed/generated on this machine. On a new machine, run `composer install`, `npm ci`, copy `.env.example` to `.env`, and run `php artisan key:generate` first. Create an empty `restroflow_db` database if it does not exist.

For frontend development, run `npm run dev` in a second terminal while `php artisan serve` is running. Open the Laravel URL, not the Vite port.

For XAMPP Apache, open http://localhost/1stTermProj/public/ (the old project URL redirects there). A virtual host should use this project's `public` directory as its document root. Apache needs mod_rewrite and AllowOverride enabled.

## Features

- Fortify login, registration, logout, remember-me, validation, and login throttling.
- Spatie roles and CRUD permissions, enforced by Laravel `can` middleware and reflected in React controls.
- React/Inertia page resolution, shared authentication props, and reusable components.
- Dashboard counts, low-stock alerts, and recent records.
- Orders: create, search, edit, delete; menu prices determine totals on the server.
- Reservations: create, search by customer/phone, edit, delete.
- Inventory: create, search, edit, delete; quantity at or below the reorder level is low stock.
- Laravel validation, CSRF-protected writes, loading/error states, and accessible Bootstrap forms.

## Database preservation

The restaurant migration creates missing tables and leaves existing tables and records untouched. The seeder only adds sample menus/tables when each lookup table is empty. Models use the original table/column names and do not require `updated_at` columns.

The configured MySQL service was unavailable during implementation, so migrations have not been applied to that database and its exact schema could not be verified. Start MySQL and run `php artisan migrate --seed` before using authentication. Automated tests use isolated SQLite databases. Back up your existing database before migrating. Do not use `migrate:fresh` or `migrate:refresh` on your existing data. The restaurant migration intentionally refuses rollback because adopted tables may contain original records. The role seeder synchronizes the three application roles and their permissions.

The original source is preserved under `legacy/`. It is a reference backup and is not used by the Laravel application.

## Main files

- `resources/js/app.jsx`: Inertia initialization and React page resolution.
- `resources/js/Pages/`: restaurant workspace and Fortify authentication pages.
- `resources/js/Components/`: reusable table, record form, auth layout, and auth field.
- `resources/js/lib/`: JSON request helper and restaurant formatting/constants.
- `resources/css/app.css`: preserved RestroFlow styling.
- `resources/views/app.blade.php`: Inertia root template and Vite assets.
- `routes/web.php`: protected page and JSON routes with authentication and permission checks.
- `config/fortify.php`: web guard and registration configuration.
- `app/Providers/FortifyServiceProvider.php`: auth views, registration action, and login rate limiter.
- `app/Actions/Fortify/CreateNewUser.php`: validated registration with mandatory Viewer role.
- `app/Http/Middleware/HandleInertiaRequests.php`: shared user, roles, permissions, and URLs.
- `app/Http/Controllers/RestaurantController.php`: validation and CRUD endpoints.
- `app/Models/`: Eloquent models.
- `database/migrations/`: database setup.
- `database/seeders/DatabaseSeeder.php`: sample lookup records for empty tables.
- `database/seeders/RolesAndPermissionsSeeder.php`: roles and CRUD permission definitions.
- `routes/console.php`: server-only `app:set-role` command.
- `.env`: Laravel settings (replaces the old `config.php`).

Page flow: Laravel/Fortify route → Inertia response → React page. Auth forms use Inertia `useForm`. CRUD flow: React form → fetch → auth/permission middleware → controller / Eloquent → MySQL → JSON → React state. Fetch reads the current XSRF cookie so requests remain valid after login rotates the session token.

Password reset email, email verification, two-factor authentication, and passkeys are not enabled.

## Verify

```powershell
php artisan test
npm run build
```

Tests cover CRUD, search, invalid input, missing records, server-side totals, low-stock counts, and preservation of existing records during table adoption. Test database settings in `phpunit.xml` force SQLite in memory, so tests do not touch MySQL.

Authentication tests also cover registration validation, password hashing, role escalation attempts, login/logout, throttling, Inertia props, guest restrictions, and direct API permission enforcement.

Run the browser integration test after building assets:

```powershell
npx playwright install chromium
npx playwright test
```

It starts a test server on port 8019 with an isolated `storage/framework/browser-auth.sqlite` database and test-only accounts. It checks login, registration, logout, inventory CRUD, role-dependent buttons, and mobile navigation. The test database and output are ignored by Git; `.env` and MySQL are unchanged.

## Presentation and submission

See [the 6 minute 30 second presentation script and checklist](docs/PRESENTATION.md) for demonstration order, narration, exact code files, and submission steps. Recording, upload, and the Eljinx invitation remain outstanding and require your repository and account.

References: [Fortify](https://laravel.com/docs/12.x/fortify), [Spatie permissions](https://spatie.be/docs/laravel-permission/v6/introduction), [Inertia](https://inertiajs.com/), and [Vite](https://vite.dev/guide/).
