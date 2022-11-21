<?php
/* --------------------------------Predifined constants-------------------------------- */
define('APP_ROOT', __DIR__ . '/../../');
define('VIEWS_PATH', APP_ROOT . 'resources/views/');
define('DATABASE_PATH', APP_ROOT . '/database/');
/* --------------------------------Global Helper Functions-------------------------------- */
function config($target = 'app', $key = 'null')
{
    $data = require APP_ROOT . "config/{$target}.php";
    if (isset($data[$key])) {
        return $data[$key];
    } else {
        throw new Exception("Key:($key) Not Found", 1);
    }
}
function DB()
{
    return (new \system\database\Database())->connection;
}
function SQL()
{
    return (new \system\database\Database())->sql;
}
function out($text)
{
    echo htmlspecialchars($text);
}
function set_csrf()
{
    if (!isset($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(50));
    }
    echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
}
function is_csrf_valid()
{
    if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) {
        return false;
    }
    if ($_SESSION['csrf'] != $_POST['csrf']) {
        return false;
    }
    return true;
}
function view($view, $data = [])
{
    $file = str_replace('.', '/', $view);
    $file = VIEWS_PATH . $file . '.php';
    if (file_exists($file)) {
        extract($data);
        include $file;
    } else {
        throw new Exception("{$view} View Not Found", 1);
    }
}
function asset($location)
{
    $url = (substr($_ENV['APP_URL'], -1) == '/') ? $_ENV['APP_URL'] : $_ENV['APP_URL'] . '/';
    echo $url . 'asset/' . $location;
}

function env($key, $value = null)
{
    $_ENV[$key] = (isset($_ENV[$key])) ? $_ENV[$key] : null;
    if ($value !== null) {
        return $_ENV[$key] = $value;
    } else {
        return $_ENV[$key];
    }
}
function is_assoc(array $arr)
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}