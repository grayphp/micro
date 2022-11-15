<?php

use system\router\Route;
use app\web\controller\WelcomeController;

Route::get('/', WelcomeController::class);