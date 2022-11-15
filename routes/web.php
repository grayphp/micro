<?php

use app\web\router\Route;
use app\web\controller\WelcomeController;

Route::get('/', WelcomeController::class, 'index');