# Email OTP setup

Registration requires a six-digit email code before creating an authenticated session. After verification, future logins use only email/username and password. An unverified account must finish registration verification before signing in; Admin accounts remain exempt. Codes expire after 10 minutes. Resending is limited to once per minute per account; verification is limited to five attempts per account per 10 minutes. A successful verification consumes the code and marks the email as verified.

Laravel sends the email from PHP using its configured mail transport. Configure your provider's SMTP credentials in `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS="your-verified-sender@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Use the host, port, scheme, credentials, and authorized sender supplied by your email provider. Then run `php artisan config:clear`. No queue worker or database migration is required for OTP.

With `MAIL_MAILER=log`, messages go to `storage/logs/laravel.log` for local development; they are not delivered to an inbox. Use SMTP for actual delivery. Do not commit real credentials.

If delivery fails after registration, the account exists but remains signed out. The user can resend on the code screen or sign in again after fixing mail configuration.

Run `php artisan test` and `npm run build` to validate the implementation.
