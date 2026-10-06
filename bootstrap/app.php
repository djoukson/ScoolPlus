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
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'role.admin' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\NoCache::class,
            \App\Http\Middleware\EnsureSessionVersion::class,
            \App\Http\Middleware\EnsurePasswordChange::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $exception) {
            return response()->view('errors.429', [], 429, $exception->getHeaders());
        });
    })
    ->create();
