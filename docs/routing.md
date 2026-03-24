# Routing

Routes are defined in `routes/web.php`.  Each call registers the route into an internal registry; after all routes have been registered, `Route::dispatch()` (called automatically by the bootstrap) matches the current request and invokes the appropriate handler.

---

## Basic Routes

```php
use system\router\Route;

Route::get('/hello', fn() => out('Hello, world!'));

Route::post('/contact', [ContactController::class, 'send']);

Route::put('/users/$id', [UserController::class, 'update']);

Route::patch('/users/$id', [UserController::class, 'patch']);

Route::delete('/users/$id', [UserController::class, 'destroy']);

Route::any('/ping', fn() => out('pong'));          // matches any HTTP method
```

---

## Dynamic Segments

Prefix a route segment with `$` to capture it as a parameter.  Captured values are passed as positional arguments to the handler.

```php
Route::get('/users/$id', function (string $id): void {
    out("User ID: $id");
});

Route::get('/posts/$year/$slug', [PostController::class, 'show']);

// In the controller:
// public function show(string $year, string $slug): void { ... }
```

---

## Controller Routes

Pass a two-element array `[ClassName::class, 'methodName']`.  The class is instantiated fresh for each request.

```php
use app\web\controller\PostController;

Route::get('/posts',      [PostController::class, 'index']);
Route::get('/posts/$id',  [PostController::class, 'show']);
Route::post('/posts',     [PostController::class, 'store']);
Route::delete('/posts/$id', [PostController::class, 'destroy']);
```

---

## View Routes

Shorthand for routes that only need to return a view:

```php
Route::view('/about', 'about');                   // renders resources/views/about.php
Route::view('/docs',  'docs.index', ['v' => 2]);  // passes $v = 2 to the template
```

---

## Redirect Routes

```php
Route::redirect('/old-path', '/new-path');         // 302 by default
Route::redirect('/moved',    '/permanent', 301);
```

---

## Named Routes

Assign a name to a route and generate its URL anywhere in the application.

```php
Route::get('/users/$id', [UserController::class, 'show'], name: 'user.show');
```

```php
// Generate the URL:
$url = url('user.show', ['id' => 42]);   // e.g. http://localhost:4000/users/42

// Or directly:
$url = Route::url('user.show', ['id' => 42]);
```

---

## Route Groups

Group routes under a shared URL prefix and/or middleware stack:

```php
Route::group('/api/v1', function (): void {
    Route::get('/users',     [UserController::class, 'index']);
    Route::post('/users',    [UserController::class, 'store']);
    Route::get('/users/$id', [UserController::class, 'show']);
}, middleware: [ApiAuthMiddleware::class]);
```

Groups can be nested:

```php
Route::group('/admin', function (): void {

    Route::group('/reports', function (): void {
        Route::get('/sales', [ReportController::class, 'sales']);
    });

}, middleware: [AdminMiddleware::class]);
```

---

## Middleware on Individual Routes

```php
Route::get('/dashboard', [DashboardController::class, 'index'],
    middleware: [AuthMiddleware::class]
);
```

Multiple middleware classes are executed in array order:

```php
Route::post('/admin/users', [UserController::class, 'store'],
    middleware: [AuthMiddleware::class, AdminMiddleware::class]
);
```

---

## Method Spoofing (HTML Forms)

HTML forms only support `GET` and `POST`.  Add a hidden `_method` field to spoof `PUT`, `PATCH`, or `DELETE`:

```html
<form method="POST" action="/posts/5">
    <?php set_csrf() ?>
    <input type="hidden" name="_method" value="DELETE">
    <button>Delete</button>
</form>
```

---

## CSRF Protection

All state-changing routes (`POST`, `PUT`, `PATCH`, `DELETE`) require a valid CSRF token.  Use `set_csrf()` inside your HTML forms:

```html
<form method="POST" action="/contact">
    <?php set_csrf() ?>
    ...
</form>
```

For AJAX requests, pass the token in the `X-CSRF-TOKEN` request header:

```js
fetch('/api/users', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    body: JSON.stringify(data),
});
```

Render the token into a meta tag in your layout:

```html
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
```
