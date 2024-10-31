<?php

use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\IsAdmin;
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
    ->withMiddleware(function (Middleware $middleware) {
      // Register your middleware here
      $middleware->alias([
        'is_admin' => IsAdmin::class,
        'cors' => CorsMiddleware::class, // Register the CORS middleware
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
