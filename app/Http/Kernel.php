<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Les middlewares globaux exécutés pour chaque requête.
     *
     * @var array
     */
    protected $middleware = [
        // Middleware globaux Laravel
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustHosts::class,
        \Illuminate\Session\Middleware\StartSession::class, // Important pour la session
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ];

    /**
     * Groupes de middleware pour les routes.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
//            \App\Http\Middleware\CheckLicense::class,

        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Middlewares assignables aux routes individuellement.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth.session' => \App\Http\Middleware\RedirectIfNotAuthenticated::class, // ton middleware personnalisé
        'role.admin' => \App\Http\Middleware\EnsureAdminRole::class,
        'role' => \App\Http\Middleware\EnsureRole::class,
        'guest' => \App\Http\Middleware\RedirectIfNotAuthenticated::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'test.middleware' => \App\Http\Middleware\TestMiddleware::class,


    ];
}
