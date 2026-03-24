# Helper Functions

All helpers are automatically available everywhere — no import needed.

---

## Configuration

### `config(string $target, string $key): mixed`

Read a value from a config file in `config/`.

```php
$appName = config('app', 'name');
$dbHost  = config('database', 'connections')['mysql']['host'];
```

Config values are cached in memory after the first read.

---

### `env(string $key, mixed $default = null): mixed`

Read an environment variable.  String literals `"true"`, `"false"`, `"null"` are cast to their PHP equivalents.

```php
$debug = env('APP_DEBUG');         // bool true / false
$dsn   = env('DATABASE_URL', ''); // string
```

---

## Database

### `DB(): \SimpleCrud\Database`

Return the singleton SimpleCrud connection.

```php
$posts = DB()->post->select()->orderBy('id DESC')->limit(10)->get();
```

### `SQL(): \PDO`

Return the singleton raw PDO connection.

```php
$count = SQL()->query('SELECT COUNT(*) FROM users')->fetchColumn();
```

---

## HTTP

### `request(): \system\http\Request`

Return the current HTTP request object.

```php
$email = request()->input('email');
```

### `redirect(string $url, int $status = 302): never`

Redirect and exit.

```php
redirect('/login');
redirect('/profile', 301);
```

### `abort(int $status, string $message = ''): never`

Send an HTTP error response and exit.

```php
abort(404);
abort(403, 'Access denied.');
```

### `json_response(mixed $data, int $status = 200): never`

Send a JSON response and exit.

```php
json_response(['users' => $users]);
json_response(['error' => 'Not found'], 404);
```

---

## Views & Assets

### `view(string $view, array $data = []): void`

Render a view template.  Uses dot-notation for nested paths.

```php
view('welcome');
view('posts.show', ['post' => $post]);
// → resources/views/posts/show.php
```

### `asset(string $location): string`

Return the URL for a public asset.

```php
<link href="<?= asset('css/app.css') ?>">
<script src="<?= asset('js/app.js') ?>"></script>
```

### `url(string $name, array $params = []): string`

Generate a URL for a named route.

```php
$link = url('user.show', ['id' => 42]);
// → http://localhost:4000/users/42
```

---

## Output Escaping

### `out(string $text): void`

Echo an HTML-escaped string (prevents XSS).

```php
out($user->name);
```

### `e(string $text): string`

Return an HTML-escaped string.

```php
<p><?= e($comment->body) ?></p>
```

---

## CSRF

### `set_csrf(): void`

Echo a hidden CSRF token `<input>` tag.  Call inside every HTML form.

```html
<form method="POST" action="/submit">
    <?php set_csrf() ?>
    ...
</form>
```

### `csrf_token(): string`

Return the CSRF token string (e.g. for meta tags or AJAX headers).

```html
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
```

### `is_csrf_valid(): bool`

Verify the CSRF token from the current request.  Called automatically by the router for state-changing verbs.

---

## Flash Messages

### `flash(string $key, mixed $value = null): mixed`

Store or retrieve a one-time flash value.

```php
// Store
flash('success', 'Your profile was updated.');
redirect('/profile');

// Retrieve (clears the value)
$msg = flash('success');
```

### `old(string $key, mixed $default = ''): mixed`

Retrieve old POST input (useful for repopulating forms after a failed validation).

```php
<input name="email" value="<?= e(old('email')) ?>">
```

Populate `$_SESSION['_old_input']` in your controller before redirecting:

```php
$_SESSION['_old_input'] = $request->body;
redirect('/form');
```

---

## Arrays

### `is_assoc(array $arr): bool`

Return `true` if the array has at least one string key.

```php
is_assoc(['a' => 1]);  // true
is_assoc([1, 2, 3]);   // false
```
