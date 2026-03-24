# Controllers

Controllers live in `app/web/controller/` and must extend `system\controller\Controller`.

---

## Generating a Controller

```bash
php dev make:controller PostController
```

This creates `app/web/controller/PostController.php`:

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

---

## Rendering Views

```php
public function show(string $id): void
{
    $post = DB()->post[(int) $id];

    if ($post === null) {
        $this->abort(404);
    }

    $this->view('posts.show', ['post' => $post]);
}
```

---

## JSON Responses

```php
public function index(): void
{
    $users = DB()->user->select()->get();

    $this->json(['data' => $users]);
}

public function store(): void
{
    $errors = $this->request()->validate([
        'name'  => 'required|min:2|max:100',
        'email' => 'required|email',
    ]);

    if ($errors) {
        $this->json(['errors' => $errors], 422);
    }

    // ... create user
    $this->json(['message' => 'Created'], 201);
}
```

---

## Redirects

```php
public function store(): void
{
    // ... save data
    $this->redirect('/posts');
}

public function update(string $id): void
{
    // ... update
    $this->redirect(url('post.show', ['id' => $id]));
}
```

---

## Aborting with HTTP Errors

```php
public function show(string $id): void
{
    $post = DB()->post[(int) $id];

    if ($post === null) {
        $this->abort(404);
    }

    // ...
}

public function edit(string $id): void
{
    if (!$this->isAdmin()) {
        $this->abort(403, 'Admins only.');
    }
    // ...
}
```

---

## Accessing the Request

```php
public function store(): void
{
    $req = $this->request();

    $name  = $req->input('name');
    $email = $req->input('email');
    $ip    = $req->ip;

    // Validate
    $errors = $req->validate([
        'name'  => 'required|max:100',
        'email' => 'required|email',
    ]);

    if ($errors) {
        $this->json(['errors' => $errors], 422);
    }

    // ...
}
```

See [Request & Response](request-response.md) for the full API.

---

## Base Controller Methods

| Method | Description |
|--------|-------------|
| `$this->view(string $template, array $data = [])` | Render a view template |
| `$this->json(mixed $data, int $status = 200)` | Send JSON and exit |
| `$this->redirect(string $url, int $status = 302)` | Redirect and exit |
| `$this->abort(int $status, string $message = '')` | HTTP error response and exit |
| `$this->request()` | Return the current `Request` instance |
