<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Restrict administrative operations to school administrators.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            in_array($request->user()?->role, ['admin', 'directeur'], true),
            403,
            'Accès réservé à l’administration.'
        );

        return $next($request);
    }
}
