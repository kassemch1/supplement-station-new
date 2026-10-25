<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Email apps' one-click unsubscribe has no CSRF token; the signed address protects it
        $middleware->validateCsrfTokens(except: ['newsletter/unsubscribe/*']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
