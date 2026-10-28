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
        // Ready-made store pages (App\Services\PageCache)
        $middleware->web(append: [\App\Http\Middleware\CachePages::class]);
        // A tunnel on this computer (cloudflared, for trying the site on a phone over https) says the visit
        // came in over https, so links are made with https. Only a proxy on the server itself is believed.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
