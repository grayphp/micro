<?php

/******************* App initiallize *********************/
ob_start();
session_start();
require_once __DIR__ . "/../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
if ($_ENV["APP_DEBUG"] === 'true') {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
} else {
    ini_set('display_errors', 0);
}
date_default_timezone_set($_ENV['TIME_ZONE']);
require_once APP_ROOT . 'routes/web.php';
\system\router\Route::any('/404', '404');