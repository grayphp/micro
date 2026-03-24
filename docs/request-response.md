# Request & Response

---

## Request

The `Request` class wraps the current HTTP request.  Access it via the `request()` helper or `$this->request()` inside a controller.

```php
$req = request();
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$req->method` | `string` | HTTP verb in uppercase (`GET`, `POST`, …) |
| `$req->path` | `string` | URL path (e.g. `/users/5`) |
| `$req->ip` | `string` | Client IP address |
| `$req->query` | `array` | `$_GET` data |
| `$req->body` | `array` | `$_POST` data |
| `$req->files` | `array` | `$_FILES` data |
| `$req->headers` | `array` | Lowercase header names → values |

### Reading Input

```php
// From POST body or GET query string (body takes priority)
$name = $req->input('name');
$name = $req->input('name', 'default');

// Specifically from query string
$page = $req->get('page', 1);

// Specifically from POST body
$email = $req->post('email');

// Check existence
if ($req->has('token')) { ... }

// All merged input
$all = $req->all();

// Only certain keys
$data = $req->only(['name', 'email']);

// Exclude certain keys
$safe = $req->except(['password', 'csrf']);
```

### JSON Body

```php
// Decode application/json request body
$payload = $req->json();
$name    = $payload['name'] ?? '';
```

### Headers

```php
$type  = $req->header('content-type');
$token = $req->header('x-api-key');

// Bearer token from Authorization header
$bearer = $req->bearerToken();
```

### Introspection

```php
$req->isAjax();    // true if X-Requested-With: XMLHttpRequest
$req->isJson();    // true if Content-Type contains application/json
$req->isSecure();  // true for HTTPS connections
$req->url();       // full URL of the current request
```

### Validation

`validate()` returns an array of error messages keyed by field name.  An **empty array** means all rules passed.

```php
$errors = $req->validate([
    'username' => 'required|min:3|max:50',
    'email'    => 'required|email',
    'website'  => 'url',
    'age'      => 'numeric',
]);

if ($errors) {
    // Return errors or re-render form
    $this->json(['errors' => $errors], 422);
}
```

**Available rules:**

| Rule | Description |
|------|-------------|
| `required` | Field must be present and non-empty |
| `min:N` | String must be at least N characters |
| `max:N` | String must not exceed N characters |
| `email` | Must be a valid email address |
| `numeric` | Must be numeric |
| `url` | Must be a valid URL |

Combine rules with `|`:  `'required|email|max:100'`

---

## Response

### JSON

```php
// Via helper
json_response(['status' => 'ok']);
json_response(['error' => 'Not found'], 404);

// Via Response class
use system\http\Response;
Response::json(['data' => $items], 200);
```

### Redirect

```php
redirect('/dashboard');
redirect('/login', 302);
redirect(url('user.show', ['id' => $id]));
```

### Abort with Error Page

```php
abort(403);
abort(404, 'Post not found.');
abort(500);
```

### Plain / HTML Response

```php
use system\http\Response;

Response::make('<p>Hello</p>', 200, ['Content-Type' => 'text/html']);
```

### File Download

```php
use system\http\Response;

Response::download('/path/to/report.pdf', 'monthly-report.pdf');
```

### Custom Headers

```php
header('Cache-Control: no-cache, no-store');
header('Content-Language: en');
```
