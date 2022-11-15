<?php

namespace system\router;

class Route
{
    static function view($route, $view, $data = [])
    {
        self::view_route($route, $view, $data);
    }

    static function get($route, $controller, $method = 'index')
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            self::route($route, $controller, $method);
        }
    }
    static function post($route, $controller, $method = 'index')
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_csrf_valid()) {
            self::route($route, $controller, $method);
        }
    }
    static function put($route, $controller, $method = 'index')
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            self::route($route, $controller, $method);
        }
    }
    static function patch($route, $controller, $method = 'index')
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
            self::route($route, $controller, $method);
        }
    }
    static function delete($route, $controller, $method = 'index')
    {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            self::route($route, $controller, $method);
        }
    }
    static function any($route, $controller, $method = null)
    {

        self::route($route, $controller, $method);
    }

    static function route($route, $controller, $method = 'index')
    {
        $callback = $controller;
        if ($route == "/404") {
            $file = VIEWS_PATH . "/$controller.php";
            if (file_exists($file)) {
                include $file;
            } else {
                echo "404 Not Found";
            }
            header("HTTP/1.0 404 Not Found");
            exit();
        }
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $rooturl = rtrim($_ENV['APP_URL'], '/');
        $request_url = filter_var(explode($rooturl, $actual_link)[1], FILTER_SANITIZE_URL);
        //$request_url = rtrim($request_url, '/');
        $request_url = strtok($request_url, '?');
        $route_parts = explode('/', $route);
        $request_url_parts = explode('/', $request_url);
        array_shift($route_parts);
        array_shift($request_url_parts);
        if ($route_parts[0] == '' && count($request_url_parts) == 0) {
            exit();
        }
        if (count($route_parts) != count($request_url_parts)) {
            return;
        }
        $parameters = [];
        for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) {
            $route_part = $route_parts[$__i__];
            if (preg_match("/^[$]/", $route_part)) {
                $route_part = ltrim($route_part, '$');
                array_push($parameters, $request_url_parts[$__i__]);
                $$route_part = $request_url_parts[$__i__];
            } else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
                return;
            }
        }
        // Callback function
        if (is_callable($callback)) {
            call_user_func_array($callback, $parameters);
            exit();
        }
        call_user_func_array([new $controller, $method], $parameters);
        exit();
    }

    static function view_route($route, $view, $data = [])
    {
        $callback = $view;
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $rooturl = rtrim($_ENV['APP_URL'], '/');
        $request_url = filter_var(explode($rooturl, $actual_link)[1], FILTER_SANITIZE_URL);
        $request_url = rtrim($request_url, '/');
        $request_url = strtok($request_url, '?');
        $route_parts = explode('/', $route);
        $request_url_parts = explode('/', $request_url);
        array_shift($route_parts);
        array_shift($request_url_parts);
        if ($route_parts[0] == '' && count($request_url_parts) == 0) {
            VIEWS_PATH . "/$view";
            exit();
        }
        if (count($route_parts) != count($request_url_parts)) {
            return;
        }
        $parameters = [];
        for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) {
            $route_part = $route_parts[$__i__];
            if (preg_match("/^[$]/", $route_part)) {
                $route_part = ltrim($route_part, '$');
                array_push($parameters, $request_url_parts[$__i__]);
                $$route_part = $request_url_parts[$__i__];
            } else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
                return;
            }
        }
        // Callback function
        if (is_callable($callback)) {
            call_user_func_array($callback, $parameters);
            exit();
        }
        view($view, $data);
        exit();
    }
}