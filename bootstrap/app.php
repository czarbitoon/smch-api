<?php

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
        // Apply CORS middleware to all routes
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);
        
        // Remove statefulApi() - it conflicts with Bearer token auth
        // API routes use Bearer token authentication, not CSRF/session-based auth
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();