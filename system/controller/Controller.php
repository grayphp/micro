<?php

declare(strict_types=1);

namespace system\controller;

use system\http\Request;
use system\http\Response;

/**
 * Base controller.
 *
 * All application controllers should extend this class.
 * It provides convenience wrappers for the most common response types
 * and access to the current HTTP request.
 */
abstract class Controller
{
    /**
     * Render a view template.
     *
     * @param array<string, mixed> $data Variables made available inside the template.
     */
    protected function view(string $template, array $data = []): void
    {
        view($template, $data);
    }

    /**
     * Send a JSON response and terminate.
     */
    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    /**
     * Redirect to a URL and terminate.
     */
    protected function redirect(string $url, int $status = 302): never
    {
        Response::redirect($url, $status);
    }

    /**
     * Abort with an HTTP error response and terminate.
     */
    protected function abort(int $status, string $message = ''): never
    {
        Response::abort($status, $message);
    }

    /**
     * Return the current HTTP request.
     */
    protected function request(): Request
    {
        return Request::current();
    }
}
