# Configuration

---

## Environment File

Copy `.env.example` to `.env` and fill in your values.  The `.env` file is **never** committed to version control.

```bash
cp .env.example .env
```

```ini
APP_NAME="My App"
APP_ENV=local          # local | staging | production
APP_DEBUG=true         # true → Whoops debug pages; false → error.log + friendly pages
APP_URL=http://localhost:4000
APP_LOCALE=en

TIME_ZONE=UTC

DB_CONNECTION=mysql    # mysql | sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=secret
DB_SOCKET=
```

---

## Reading Config Values

Use the `config()` helper.  Results are cached in memory per-request.

```php
$name     = config('app', 'name');
$timezone = config('app', 'timezone');
$dbHost   = config('database', 'connections')['mysql']['host'];
```

---

## Application Config (`config/app.php`)

| Key | Default | Description |
|-----|---------|-------------|
| `name` | `"Micro Framework"` | Application name |
| `env` | `"production"` | Runtime environment |
| `debug` | `false` | Enable debug mode |
| `url` | `"http://localhost"` | Application base URL |
| `timezone` | `"UTC"` | Default PHP timezone |
| `locale` | `"en"` | Default locale |
| `charset` | `"UTF-8"` | Character encoding |

---

## Database Config (`config/database.php`)

| Key | Default | Description |
|-----|---------|-------------|
| `default` | `"mysql"` | Active connection driver |
| `connections.mysql.host` | `127.0.0.1` | MySQL host |
| `connections.mysql.port` | `3306` | MySQL port |
| `connections.mysql.database` | _empty_ | Database name |
| `connections.mysql.username` | `root` | Database user |
| `connections.mysql.password` | _empty_ | Database password |
| `connections.mysql.charset` | `utf8mb4` | Character set (do not change) |
| `connections.mysql.unix_socket` | _empty_ | Unix socket path (optional) |
| `connections.sqlite.database` | `database/database.sqlite` | SQLite file path |

---

## Custom Config Files

Create any PHP file in `config/` that returns an array:

```php
// config/mail.php
return [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host'   => env('MAIL_HOST',   'smtp.mailtrap.io'),
    'port'   => env('MAIL_PORT',   2525),
    'from'   => env('MAIL_FROM',   'noreply@example.com'),
];
```

Then read it anywhere:

```php
$host = config('mail', 'host');
```

---

## Debug Mode

When `APP_DEBUG=true`:
- [Whoops](https://github.com/filp/whoops) pretty error pages are displayed
- Stack traces expose file paths and source code

When `APP_DEBUG=false`:
- Errors are logged to `storage/logs/error.log`
- Users see the `resources/views/500.php` template
- No sensitive information is leaked

> **Always set `APP_DEBUG=false` in production.**

---

## Security Headers

The bootstrap automatically adds these headers on every response:

| Header | Value |
|--------|-------|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |

Add additional headers in `boot/bootstrap.php` or inside a global middleware.
