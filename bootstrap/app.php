<?php

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof ValidationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 422,$e->errors());
                }
                if ($e instanceof ModelNotFoundException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 404);
                }
                if ($e instanceof AuthenticationException) {
                    return Helper::jsonErrorResponse( $e->getMessage(), 401);
                }
                if ($e instanceof AuthorizationException) {
                    return Helper::jsonErrorResponse( $e->getMessage(), 403);
                }
                // Dynamically determine the status code if available
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return Helper::jsonErrorResponse($e->getMessage(), $statusCode);
            }
            return null;
        });
    })->create();
