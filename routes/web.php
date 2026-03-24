<?php

declare(strict_types=1);

use system\router\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Register your application routes here.  Routes are matched top-to-bottom;
| the first match executes and the script exits.
|
| Available methods: get  post  put  patch  delete  any  view  redirect  group
|
| Examples:
|   Route::get('/hello', fn() => out('Hello world'));
|   Route::get('/user/$id', fn(string $id) => out("User: $id"), name: 'user.show');
|   Route::get('/about', 'about', name: 'about');          // renders resources/views/about.php
|   Route::get('/profile', [ProfileController::class, 'show'], middleware: [AuthMiddleware::class]);
|
|   Route::group('/api/v1', function () {
|       Route::get('/users', [UserController::class, 'index']);
|       Route::post('/users', [UserController::class, 'store']);
|   });
|
*/

Route::get('/', function (): void {
    view('welcome');
});
