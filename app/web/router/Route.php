<?php

namespace app\web\router;

class Route
{
    static function view($route, $view, $data = [])
    {
        self::view_route($route, $view, $data);
    }

    static function get($route, $controller, $method = 'index')
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            self::route($route, $controller, $method);
        }
    }
    static function post($route, $controller)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            self::route($route, $controller);
        }
    }
    static function put($route, $controller)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            self::route($route, $controller);
        }
    }
    static function patch($route, $controller)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
            self::route($route, $controller);
        }
    }
    static function delete($route, $controller)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            self::route($route, $controller);
        }
    }
    static function any($route, $controller, $method = null)
    {
        if ($method == null) {
            self::route($route, $controller);
        } else {
            self::route($route, $controller, $method);
        }
    }

    static function route($route, $controller, $method = 'index')
    {
        $callback = $controller;
        if ($route == "/404") {
            include_once VIEWS_PATH . "/$controller";
            exit();
        }
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
            VIEWS_PATH . "/$controller";
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
        (new $controller())->$method();
        exit();
    }

    static function view_route($route, $view, $data = [])
    {
        $callback = $view;
        if ($route == "/404") {
            include_once VIEWS_PATH . "/$view";
            exit();
        }
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