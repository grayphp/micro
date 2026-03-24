# Database

Micro uses [SimpleCrud](https://github.com/oscarotero/simple-crud) as its database ORM, with PDO under the hood.

---

## Configuration

Edit `config/database.php` and set your credentials in `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=secret
```

For SQLite:

```ini
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

---

## Accessing the Database

```php
$db = DB();   // SimpleCrud Database instance (singleton)
$pdo = SQL(); // Raw PDO connection (singleton)
```

---

## Basic CRUD

### Read

```php
// By primary key
$post = DB()->post[5];

// Check existence
if (isset(DB()->post[5])) { ... }

// Count rows
$total = count(DB()->post);
```

### Create

```php
// Insert
DB()->post[] = ['title' => 'Hello World', 'body' => '...'];

// Insert and get new row
$post = DB()->post->create(['title' => 'Hello'])->save();
```

### Update

```php
// Update by id
DB()->post[5] = ['title' => 'Updated Title'];

// Update via row object
$post = DB()->post[5];
$post->title = 'New Title';
$post->save();
```

### Delete

```php
// Delete by id
unset(DB()->post[5]);

// Delete via row object
$post = DB()->post[5];
$post->delete();
```

---

## Queries

```php
// Select with conditions
$posts = DB()->post
    ->select()
    ->where('status = ', 'published')
    ->orderBy('created_at DESC')
    ->limit(10)
    ->get();

foreach ($posts as $post) {
    echo $post->title;
}

// Select first matching row
$post = DB()->post
    ->select()
    ->one()
    ->where('slug = ', 'my-post')
    ->get();

// Select by field
$user = DB()->user->get(['email' => 'user@example.com']);

// Select or create
$tag = DB()->tag->getOrCreate(['slug' => 'php']);
```

### Aggregates

```php
$count = DB()->post->selectAggregate('COUNT')->get();
$sum   = DB()->post->selectAggregate('SUM', 'views')->get();
```

### Update Query

```php
DB()->post
    ->update(['status' => 'archived'])
    ->where('created_at < ', '2023-01-01')
    ->get();
```

### Delete Query

```php
DB()->post
    ->delete()
    ->where('id = ', 42)
    ->get();
```

### Insert Query

```php
$id = DB()->post
    ->insert(['title' => 'Hello', 'body' => 'World'])
    ->get();
```

---

## Pagination

```php
$query = DB()->post
    ->select()
    ->where('status = ', 'published')
    ->orderBy('created_at DESC')
    ->page(1)
    ->perPage(20);

$posts = $query->get();
$info  = $query->getPageInfo();

// $info['totalRows']    → 125
// $info['totalPages']   → 7
// $info['currentPage']  → 1
// $info['previousPage'] → null
// $info['nextPage']     → 2
```

---

## Relationships & Lazy Loading

```php
// One-to-many
$post     = DB()->post[34];
$comments = $post->comment;  // auto-fetched, cached

// Many-to-many
$post = DB()->post[34];
$tags = $post->tag;

// Relate / unrelate
$post->relate($comment);
$post->unrelate($comment);
$post->unrelateAll(DB()->comment);
```

---

## Solving the N+1 Problem

Preload related rows in a single query before iterating:

```php
$posts = DB()->post->select()->get();

// Preload categories for all posts in one query
$posts->category;

foreach ($posts as $post) {
    echo $post->category->name;  // no extra query
}
```

For many-to-many with custom ordering:

```php
$posts    = DB()->post->select()->get();
$postTags = $posts->post_tag()->get();
$tags     = $postTags->tag()->orderBy('name ASC')->get();
$posts->link($tags, $postTags);

foreach ($posts as $post) {
    foreach ($post->tag as $tag) {
        echo $tag->name;
    }
}
```

---

## Raw PDO

Use `SQL()` for queries that fall outside SimpleCrud:

```php
$stmt = SQL()->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
$stmt->execute(['admin']);
$count = $stmt->fetchColumn();
```
