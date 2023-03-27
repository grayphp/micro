# PHP Micro Framework

Think big, code small with Micro - the PHP micro framework for web applications.

## Installation

Install via composer

```bash
 composer create-project grayphp/micro my-app
 cd my-app
```

```
php dev start
```

```
http://localhost:4000
```

## Usage/Examples

#### Abailable Routes:

```
[get,post,patch,put,delete,any]
```

### Route

```php
use system\router\Route;
Route::get('/path',[controller::class,'method']);
Route::method('/path',callback);
```
### Middleware
```php
Route::any('/profile', ['controller' => HomeController::class, 'profile', 'middleware' => UserAuth::class]);
```

### Dynamic Route

```php
Route::get('/user/$id',function($id)){
    print $id;
}

```

## controller

### Make controller

```cli
php dev -c MyController
```
## Middleware

### Make Middleware

```cli
php dev -m MyMiddleware
```

## Database

Your can access to Database using DB() helper.

### Basic CRUD:

You can interact directly with the tables to insert/update/delete/select data:

Use `ArrayAccess` interface to access to the data using the `id`:

## DB instance

```php
$db = DB();
```

```php
//Get the post id = 3;
$post = $db->post[3];
//Check if a row exists
if (isset($db->post[3])) {
    echo 'exists';
}
//Delete a post
unset($db->post[3]);
//Update a post
$db->post[3] = [
    'title' => 'Hello world'
];
//Insert a new post
$db->post[] = [
    'title' => 'Hello world 2'
];
//Tables implements the Countable interface
$totalPost = count($db->post);
```

### Select by other fields

If you want to select a row by other key than `id`, just use the method `get`:

```php
$post = $db->post->get(['slug' => 'post-slug']);
```

### Select or create

Sometimes, you want to get a row or create it if it does not exist. You can do it easily with `getOrCreate` method:

```php
$post = $db->post->getOrCreate(['slug' => 'post-slug']);
```

### Rows

A `Row` object represents a database row and is used to read and modify its data:

```php
//get a row by id
$post = $db->post[34];
//Get/modify fields values
echo $post->title;
$post->title = 'New title';
//Update the row into database
$post->save();
//Remove the row in the database
$post->delete();
//Create a new row
$newPost = $db->post->create(['title' => 'The title']);
//Insert the row in the database
$newPost->save();
```

### Queries

A `Query` object represents a database query. SimpleCrud uses magic methods to create queries. For example `$db->post->select()` returns a new instance of a `Select` query in the tabe `post`. Other examples: `$db->comment->update()`, `$db->category->delete()`, etc... Each query has modifiers like `orderBy()`, `limit()`:

```php
//Create an UPDATE query with the table post
$updateQuery = $db->post->update(['title' => 'New title']);
//Add conditions, limit, etc
$updateQuery
    ->where('id = ', 23)
    ->limit(1);
//get the query as string
echo $updateQuery; //UPDATE `post` ...
//execute the query and returns a PDOStatement with the result
$PDOStatement = $updateQuery();
```

The method `get()` executes the query and returns the processed result of the query. For example, with `insert()` returns the id of the new row:

```php
//insert a new post
$id = $db->post
    ->insert([
        'title' => 'My first post',
        'text' => 'This is the text of the post'
    ])
    ->get();
//Delete a post
$db->post
    ->delete()
    ->where('id = ', 23)
    ->get();
//Count all posts
$total = $db->post
    ->selectAggregate('COUNT')
    ->get();
//note: this is the same like count($db->post)
//Sum the ids of all posts
$total = $db->post
    ->selectAggregate('SUM', 'id')
    ->get();
```

`select()->get()` returns an instance of `RowCollection` with the result:

```php
$posts = $db->post
    ->select()
    ->where('id > ', 10)
    ->orderBy('id ASC')
    ->limit(100)
    ->get();
foreach ($posts as $post) {
    echo $post->title;
}
```

If you only need the first row, use the modifier `one()`:

```php
$post = $db->post
    ->select()
    ->one()
    ->where('id = ', 23)
    ->get();
echo $post->title;
```

`select()` has some interesting modifiers like `relatedWith()` to add automatically the `WHERE` clauses needed to select data related with other row or rowCollection:

```php
//Get the post id = 23
$post = $db->post[23];
//Select the category related with this post
$category = $db->category
    ->select()
    ->relatedWith($post)
    ->one()
    ->get();
```

### Query API:

Queries use [Atlas.Query](http://atlasphp.io/cassini/query/) library to build the final queries, so you can see the documentation for all available options.

#### Select / SelectAggregate

| Function                                             | Description                                                                   |
| ---------------------------------------------------- | ----------------------------------------------------------------------------- |
| `one`                                                | Select 1 result.                                                              |
| `relatedWith(Row / RowCollection / Table $relation)` | To select rows related with other rows or tables (relation added in `WHERE`). |
| `joinRelation(Table $table)`                         | To add a related table as `LEFT JOIN`.                                        |
| `getPageInfo()`                                      | Returns the info of the pagination.                                           |
| `from`                                               | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `columns`                                            | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `join`                                               | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `catJoin`                                            | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `groupBy`                                            | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `having`                                             | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `orHaving`                                           | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `orderBy`                                            | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `catHaving`                                          | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `where`                                              | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `whereSprintf`                                       | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `catWhere`                                           | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `orWhere`                                            | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `orWhereSprintf`                                     | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `whereEquals`                                        | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `limit`                                              | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `offset`                                             | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `distinct`                                           | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `forUpdate`                                          | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `setFlag`                                            | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |
| `bindValue`                                          | [Atlas.Query Select()](http://atlasphp.io/cassini/query/select.html)          |

#### Update

| Function                                             | Description                                                                   |
| ---------------------------------------------------- | ----------------------------------------------------------------------------- |
| `relatedWith(Row / RowCollection / Table $relation)` | To update rows related with other rows or tables (relation added in `WHERE`). |
| `set`                                                | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `setFlag`                                            | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `where`                                              | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `orWhere`                                            | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `catWhere`                                           | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `orderBy`                                            | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `limit`                                              | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |
| `offset`                                             | [Atlas.Query Update()](http://atlasphp.io/cassini/query/update.html)          |

#### Insert

| Function     | Description                                                                      |
| ------------ | -------------------------------------------------------------------------------- |
| `orIgnore()` | To ignore silently the insertion on duplicated keys, instead throw an exception. |
| `set`        | [Atlas.Query Insert()](http://atlasphp.io/cassini/query/insert.html)             |
| `setFlag`    | [Atlas.Query Insert()](http://atlasphp.io/cassini/query/insert.html)             |

#### Delete

| Function                                             | Description                                                                   |
| ---------------------------------------------------- | ----------------------------------------------------------------------------- |
| `relatedWith(Row / RowCollection / Table $relation)` | To delete rows related with other rows or tables (relation added in `WHERE`). |
| `setFlag`                                            | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |
| `where`                                              | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |
| `orWhere`                                            | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |
| `catWhere`                                           | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |
| `orderBy`                                            | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |
| `limit`                                              | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |
| `offset`                                             | [Atlas.Query Delete()](http://atlasphp.io/cassini/query/delete.html)          |

### Lazy loads

Both `Row` and `RowCollection` can load automatically other related rows. Just use a property named as related table. For example:

```php
//Get the category id=34
$category = $db->category[34];
//Load the posts of this category
$posts = $category->post;
//This is equivalent to:
$posts = $db->post
    ->select()
    ->relatedWith($category)
    ->get();
//But the result is cached so the database query is executed only the first time
$posts = $category->post;
```

This allows make things like this:

```php
$titles = $db->post[34]->tag->post->title;
//Get the post id=34
//Get the tags of the post
//Then the posts related with these tags
//And finally, the titles of all these posts
```

Use magic methods to get a `Select` query returning related rows:

```php
$category = $db->category[34];
//Magic property: Returns all posts of this category:
$posts = $category->post;
//Magic method: Returns the query instead the result
$posts = $category->post()
    ->where('pubdate > ', date('Y-m-d'))
    ->limit(10)
    ->get();
```

### Solving the n+1 problem

The [n+1 problem](http://stackoverflow.com/questions/97197/what-is-the-n1-selects-issue) can be solved in the following way:

```php
//Get some posts
$posts = $db->post
    ->select()
    ->get();
//preload all categories
$posts->category;
//now you can iterate with the posts
foreach ($posts as $post) {
    echo $post->category;
}
```

You can perform the select by yourself to include modifiers:

```php
//Get some posts
$posts = $db->post
    ->select()
    ->get();
//Select the categories but ordered alphabetically descendent
$categories = $posts->category()
    ->orderBy('name DESC')
    ->get();
//Save the result in the cache and link the categories with each post
$posts->link($categories);
//now you can iterate with the posts
foreach ($posts as $post) {
    echo $post->category;
}
```

For many-to-many relations, you need to do one more step:

```php
//Get some posts
$posts = $db->post
    ->select()
    ->get();
//Select the post_tag relations
$tagRelations = $posts->post_tag()->get();
//And now the tags of these relations
$tags = $tagRelations->tag()
    ->orderBy('name DESC')
    ->get();
//Link the tags with posts using the relations
$posts->link($tags, $tagRelations);
//now you can iterate with the posts
foreach ($posts as $post) {
    echo $post->tag;
}
```

### Relate and unrelate data

To save related rows in the database, you need to do this:

```php
//Get a comment
$comment = $db->comment[5];
//Get a post
$post = $db->post[34];
//Relate
$post->relate($comment);
//Unrelate
$post->unrelate($comment);
//Unrelate all comments of the post
$post->unrelateAll($db->comment);
```

### Pagination

The `select` query has a special modifier to paginate the results:

```php
$query = $db->post->select()
    ->page(1)
    ->perPage(50);
$posts = $query->get();
//To get the page info:
$pagination = $query->getPageInfo();
echo $pagination['totalRows']; //125
echo $pagination['totalPages']; //3
echo $pagination['currentPage']; //1
echo $pagination['previousPage']; //NULL
echo $pagination['nextPage']; //2
```
