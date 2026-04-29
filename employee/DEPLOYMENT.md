# Employee Portal Deployment Notes (Hostinger)

## Target server layout

```
/public_html                  (domain root)
/superadmin/backend           (admin CI4 app root)
/superadmin/public_html       (admin web root)
/employee                     (employee CI4 app root; this folder)
/employee/public              (employee web root)
```

## Root domain -> Employee app

In `public_html/.htaccess`, rewrite **all requests** except `/superadmin/*` to the employee front controller.

Create/replace with:

```apacheconf
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /

  # Allow direct access to admin portal path
  RewriteRule ^superadmin/ - [L]

  # If requested file/folder exists in /public_html, serve it
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # Otherwise forward to employee/public
  RewriteRule ^(.*)$ employee/public/$1 [L]
</IfModule>
```

## Employee app requirements

- Ensure `employee/writable` is writable by PHP.
- Set `employee/.env` to match the same DB credentials used by admin.
- Employee sessions are isolated by cookie name `emp_session`.

## Database migration

Employee OTP requires additional columns on `employees` table (safe additive migration):
- `email`, `salary`, `otp_hash`, `otp_expires_at`, `otp_attempts`, `otp_last_sent_at`

Run (inside `/employee`):

```bash
php spark migrate
```

