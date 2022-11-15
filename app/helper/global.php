<?php
/* --------------------------------Predifined constants-------------------------------- */
define('APP_ROOT', __DIR__ . '/../../');
define('VIEWS_PATH', APP_ROOT . 'resources/views/');

/* --------------------------------Global Helper Functions-------------------------------- */
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