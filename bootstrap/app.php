<?php

use App\Http\Middleware\ForceJsonApi;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
    web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
    // Alias de middleware
    $middleware->alias([
        'role' => RoleMiddleware::class,
    ]);

    // Groupe API
    $middleware->group('api', [
        \App\Http\Middleware\ForceJsonApi::class,
      //  'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);
})
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions): void {
        //
    })->create();
