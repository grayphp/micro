# CLI

Micro includes a simple command-line tool at the project root.

```
php dev <command> [arguments]
```

---

## Commands

### `serve` / `start`

Start the built-in PHP development server on port **4000** (default):

```bash
php dev serve
php dev start
```

Use a custom port:

```bash
php dev serve 8080
php dev start 3000
```

> **Note:** The development server is not suitable for production.  Use Nginx or Apache behind PHP-FPM in production environments.

---

### `make:controller`

Scaffold a new controller class in `app/web/controller/`:

```bash
php dev make:controller PostController
```

Generated file (`app/web/controller/PostController.php`):

```php
<?php

declare(strict_types=1);

namespace app\web\controller;

use system\controller\Controller;

class PostController extends Controller
{
    public function index(): void
    {
        $this->view('welcome');
    }
}
```

The short alias `-c` is also accepted:

```bash
php dev -c UserController
```

---

### `make:middleware`

Scaffold a new middleware class in `app/web/middleware/`:

```bash
php dev make:middleware AuthMiddleware
```

Generated file (`app/web/middleware/AuthMiddleware.php`):

```php
<?php

declare(strict_types=1);

namespace app\web\middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        // Inspect the request here.
        // Call abort(403) or redirect('/login') to block the request.
    }
}
```

---

### `-h` / `--help`

Display all available commands:

```bash
php dev --help
```
