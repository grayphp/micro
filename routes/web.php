<?php

use system\router\Route;
use app\web\middleware\Test;
use app\web\controller\WelcomeController;

Route::get('/', [WelcomeController::class, 'test']);