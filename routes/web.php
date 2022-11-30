<?php

use system\router\Route;

Route::get('/', function () {
    return view('welcome');
});