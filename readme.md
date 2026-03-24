# Micro — PHP Micro Framework

A lightweight, expressive PHP micro-framework for building web applications with clean **PHP 8.2+** syntax.  No magic, no bloat — just a router, controllers, views, and a database ORM working together cleanly.

---

## Requirements

- **PHP 8.2+**
- Composer
- `ext-mbstring`, `ext-openssl`, `ext-pdo`

---

## Installation

```bash
composer create-project grayphp/micro my-app
cd my-app
cp .env.example .env
```

Edit `.env` with your app settings, then start the development server:

```bash
php dev serve          # http://localhost:4000
php dev serve 8080     # http://localhost:8080
```

---

## Directory Structure

```
my-app/
├── app/
│   └── web/
│       ├── controller/     # Application controllers
│       └── middleware/     # Request middleware
├── boot/
│   └── bootstrap.php       # Application bootstrap
├── config/
│   ├── app.php             # App configuration
│   └── database.php        # Database configuration
├── docs/                   # Full documentation
├── public/
│   └── index.php           # Web entry point
├── resources/
│   └── views/              # PHP view templates
├── routes/
│   └── web.php             # Route definitions
├── storage/
│   └── logs/               # Error logs (production)
├── system/                 # Framework core (do not modify)
│   ├── console/            # CLI commands
│   ├── controller/         # Base controller
│   ├── database/           # Database drivers
│   ├── exception/          # HTTP exception classes
│   ├── helper/             # Global helper functions
│   ├── http/               # Request / Response classes
│   └── router/             # Router
├── .env                    # Local environment (gitignored)
├── .env.example            # Template for .env
└── composer.json
```

---

## Quick Start

### 1. Define Routes (`routes/web.php`)

```php
use system\router\Route;

// Closure route
Route::get('/', fn() => view('welcome'));

// Controller route
Route::get('/posts',     [PostController::class, 'index']);
Route::get('/posts/$id', [PostController::class, 'show'], name: 'post.show');
Route::post('/posts',    [PostController::class, 'store']);

// Dynamic segments
Route::get('/users/$id/posts/$slug', function (string $id, string $slug): void {
    out("User {$id} → Post: {$slug}");
});

// Route groups with middleware
Route::group('/admin', function (): void {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
}, middleware: [AuthMiddleware::class]);

// View shorthand
Route::view('/about', 'about');

// Redirect
Route::redirect('/old', '/new');
```

### 2. Create a Controller

```bash
php dev make:controller PostController
```

```php
<?php

declare(strict_types=1);

namespace app\web\controller;

use system\controller\Controller;

class PostController extends Controller
{
    public function index(): void
    {
        $posts = DB()->post->select()->orderBy('created_at DESC')->limit(10)->get();
        $this->view('posts.index', compact('posts'));
    }

    public function show(string $id): void
    {
        $post = DB()->post[(int) $id];

        if ($post === null) {
            $this->abort(404);
        }

        $this->view('posts.show', compact('post'));
    }

    public function store(): void
    {
        $errors = $this->request()->validate([
            'title' => 'required|max:200',
            'body'  => 'required',
        ]);

        if ($errors) {
            $this->json(['errors' => $errors], 422);
        }

        DB()->post[] = $this->request()->only(['title', 'body']);
        $this->redirect('/posts');
    }
}
```

### 3. Create a View (`resources/views/posts/index.php`)

```html
<!DOCTYPE html>
<html>
<head>
    <title><?= e(config('app', 'name')) ?></title>
</head>
<body>
    <h1>Posts</h1>
    <?php foreach ($posts as $post): ?>
        <article>
            <h2><a href="<?= e(url('post.show', ['id' => $post->id])) ?>"><?= e($post->title) ?></a></h2>
        </article>
    <?php endforeach; ?>
</body>
</html>
```

---

## Documentation

| Topic | File |
|-------|------|
| Routing | [docs/routing.md](docs/routing.md) |
| Controllers | [docs/controllers.md](docs/controllers.md) |
| Request & Response | [docs/request-response.md](docs/request-response.md) |
| Database | [docs/database.md](docs/database.md) |
| Middleware | [docs/middleware.md](docs/middleware.md) |
| Helper Functions | [docs/helpers.md](docs/helpers.md) |
| CLI Commands | [docs/cli.md](docs/cli.md) |
| Configuration | [docs/configuration.md](docs/configuration.md) |

---

## Key Features

| Feature | Details |
|---------|---------|
| **PHP 8.2+** | `readonly` properties, `match`, named arguments, `declare(strict_types=1)` everywhere |
| **Router** | GET / POST / PUT / PATCH / DELETE / ANY, dynamic segments, named routes, groups, redirects, view shortcuts |
| **Controller** | Base class with `view()`, `json()`, `redirect()`, `abort()`, `request()` |
| **Request** | Immutable value object — input, headers, JSON body, validation, IP, bearer token |
| **Response** | `json()`, `redirect()`, `abort()`, `download()`, security headers |
| **Database** | SimpleCrud ORM (PDO-backed), singleton connection, MySQL + SQLite drivers |
| **CSRF** | `hash_equals()` comparison, POST/header token, `set_csrf()` / `csrf_token()` helpers |
| **Middleware** | Per-route and per-group, array-ordered stack |
| **Method Spoofing** | `_method` POST field for PUT / PATCH / DELETE from HTML forms |
| **Flash** | One-request session flash messages |
| **Error Handling** | Whoops in debug mode; structured logging + friendly error pages in production |
| **Security Headers** | Automatic on every response |
| **CLI** | `serve`, `make:controller`, `make:middleware` scaffolding commands |

---

## Security

- **CSRF tokens** — compared with `hash_equals()` (timing-safe)
- **Output escaping** — `e()` / `out()` use `htmlspecialchars` with `ENT_QUOTES | ENT_SUBSTITUTE`
- **Database** — PDO with `ERRMODE_EXCEPTION` and prepared statements via SimpleCrud
- **Security headers** — sent on every response
- **Debug mode off** — errors logged, never displayed in production

---

## License

MIT © [Sharif](https://github.com/grayphp)
