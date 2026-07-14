<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\PreventSearchIndexing;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the reverse proxy / load balancer in front of the app so
        // Laravel honors X-Forwarded-Proto and generates https:// URLs. Without
        // this, form actions built with url() point at http:// on production,
        // the proxy redirects http->https, and the browser downgrades the POST
        // to a GET (405 Method Not Allowed).
        $middleware->trustProxies(at: '*');

        $middleware->append([
            PreventSearchIndexing::class,
        ]);

        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
