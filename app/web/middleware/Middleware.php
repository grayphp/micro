<?php

declare(strict_types=1);

namespace app\web\middleware;

/**
 * Example base middleware.
 *
 * Create your own middleware classes in app/web/middleware/ and implement
 * the handle() method.  Register them on individual routes or route groups:
 *
 *   Route::get('/admin', [AdminController::class, 'index'], middleware: [AuthMiddleware::class]);
 *
 *   Route::group('/admin', function () {
 *       Route::get('/dashboard', [AdminController::class, 'dashboard']);
 *   }, middleware: [AuthMiddleware::class]);
 */
class Middleware
{
    /**
     * Execute before the route handler.
     *
     * Call abort() or redirect() to short-circuit the request.
     */
    public function handle(): void
    {
        // Example: abort(403) if user is not authenticated.
    }
}
