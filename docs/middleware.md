# Middleware

Middleware classes run **before** the route handler executes.  They are ideal for authentication checks, rate limiting, logging, and any other cross-cutting concerns.

---

## Creating Middleware

```bash
php dev make:middleware AuthMiddleware
```

This generates `app/web/middleware/AuthMiddleware.php`:

```php
<?php

declare(strict_types=1);

namespace app\web\middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        // Block the request or modify state here.
        // Call abort() or redirect() to short-circuit.
    }
}
```

### Example: Authentication Gate

```php
<?php

declare(strict_types=1);

namespace app\web\middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('/login');
        }
    }
}
```

### Example: Admin-Only Gate

```php
<?php

declare(strict_types=1);

namespace app\web\middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            abort(403, 'Admins only.');
        }
    }
}
```

---

## Attaching Middleware to Routes

### Single Route

```php
use app\web\middleware\AuthMiddleware;

Route::get('/dashboard', [DashboardController::class, 'index'],
    middleware: [AuthMiddleware::class]
);
```

### Multiple Middleware (executed in order)

```php
Route::post('/admin/users', [UserController::class, 'store'],
    middleware: [AuthMiddleware::class, AdminMiddleware::class]
);
```

### Route Group

Apply middleware to every route within a group:

```php
Route::group('/admin', function (): void {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users',     [AdminController::class, 'users']);
    Route::post('/users',    [AdminController::class, 'storeUser']);
}, middleware: [AuthMiddleware::class, AdminMiddleware::class]);
```

---

## Middleware Execution Order

1. Group middleware (outer to inner)
2. Route-level middleware (array order)
3. Controller method

---

## Accessing Request & Session in Middleware

Middleware runs within the same request lifecycle; you can use all global helpers:

```php
public function handle(): void
{
    $req = request();

    // Check API token
    $token = $req->bearerToken();

    if ($token === null || !$this->isValidToken($token)) {
        abort(401, 'Invalid or missing API token.');
    }
}
```
