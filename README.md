# URL Shortener

This repository implements a compact PHP-based URL shortener. It provides pages for creating short URLs, managing users (companies and roles), handling invitations, authenticating users, and redirecting short codes.

The following guide is written in the third person and instructs an operator on how to prepare and run the application locally.

## Repository contents
- `config.php` — establishes the PDO connection and starts PHP session handling.
- `schema.sql` — creates the `url_shortener` database and tables.
- `seed_super_admin.php` — creates a super-admin user (operators should edit values before running).
- `create_url.php`, `urls.php`, `redirect.php` — core URL creation and redirect endpoints.
- `login.php`, `logout.php`, `invite.php`, `accept_invite.php`, `auth.php`, `dashbord.php` — authentication and dashboard pages.

## Requirements
- PHP 7.4 or 8.x with the `pdo_mysql` extension enabled.
- MySQL or MariaDB server.
- (Optional) XAMPP, WAMP, or Laragon on Windows for an integrated local environment.

## Preparation steps (operator)

1. Place the project files in a convenient directory, for example `D:\coding\url-shortner` or the webroot used by the operator's local Apache (`C:\xampp\htdocs\url-shortner`).

2. Edit `config.php` to set the correct database credentials:

   - `$host`, `$db`, `$user`, and `$pass` must match the MySQL server configuration.
   - When the webserver and database are on the same machine, prefer `127.0.0.1` instead of `localhost` to force TCP connections.

3. Create the database and tables. From PowerShell in the project root the operator should run:

```powershell
cd D:/coding/url-shortner
mysql -u root -p < schema.sql
```

Alternatively, import `schema.sql` via phpMyAdmin if preferred.

4. Seed a super-admin account. The operator should update `seed_super_admin.php` with chosen credentials, then run:

```powershell
cd D:/coding/url-shortner
php seed_super_admin.php
```

The script prints the created account data when run from the CLI. If executed via a browser, the password is intentionally not printed.

5. Start the application for development:

```powershell
cd D:/coding/url-shortner
php -S localhost:8000
```

Open `http://localhost:8000/login.php` (or other endpoints such as `urls.php`) in the operator's browser.

If the operator prefers Apache (XAMPP/WAMP), they should place the project in the webroot and start Apache + MySQL via the control panel.

## Diagnostics and common fixes

If the web application shows "DB connection failed" while the MySQL client connects successfully, the operator should verify the following:

1. Check that the `pdo_mysql` extension is enabled for the web SAPI. Create `phpinfo.php` with this content and open it in the browser:

```php
<?php
phpinfo();
```

Confirm the "Loaded Configuration File" path and the presence of `pdo_mysql` in the extensions list.

2. If `pdo_mysql` is missing for the web SAPI but present in CLI, enable it in the `php.ini` identified by `phpinfo()` (Windows example):

 - Open the detected `php.ini`.
 - Ensure the line `extension=php_pdo_mysql.dll` is present and not commented out.
 - Restart Apache or the web server.

3. If the error indicates "Access denied for user", ensure the DB user has privileges for the host the webserver uses. Example SQL for a local TCP user:

```sql
CREATE USER 'webuser'@'127.0.0.1' IDENTIFIED BY 'StrongPassword!';
GRANT ALL PRIVILEGES ON url_shortener.* TO 'webuser'@'127.0.0.1';
FLUSH PRIVILEGES;
```

4. If socket or host errors persist, change `$host` in `config.php` from `localhost` to `127.0.0.1` and explicitly include the port in the DSN if necessary.

## Quick checks the operator can run

- Verify the MySQL server is running (services or via `mysql -u root -p`).
- From the project root, test DB connection via CLI PHP:

```powershell
php -r "require 'config.php'; echo 'PDO OK\n';"
```

- Use the provided `db_test.php` (if present) through the web server to confirm `pdo_mysql` is visible to the web SAPI.

## Security considerations

This project stores hashed passwords and uses prepared statements in some places, but it is not ready for production. The operator should:

- Add CSRF protection to all state-changing forms.
- Sanitize and validate all user input before use.
- Serve the application over HTTPS in production.

## Recommendations for maintainers

- Perform a repository-wide audit to ensure prepared statements are used everywhere.
- Add a `docker-compose.yml` that starts PHP + MySQL to make local setup reproducible.
- Consider adding automated tests for core behaviors (short code generation, redirect correctness, auth flows).

---

If the operator requests it, the maintainer can be supplied with further assistance to:

- scan PHP files and apply consistent error handling and prepared statements,
- create a `docker-compose.yml` for one-command local setup.

