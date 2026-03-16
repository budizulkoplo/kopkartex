<?php

use App\Http\Middleware\EnsureMenuAccess;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\GlobalApp;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'menu.access' => EnsureMenuAccess::class,
            'active.user' => EnsureUserIsActive::class,
            'global.app' => GlobalApp::class,
            'role'  => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
