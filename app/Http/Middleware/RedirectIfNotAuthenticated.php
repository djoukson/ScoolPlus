<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {

            // redirige vers login ET garde l'URL actuelle en "intended"
            return redirect()->guest(route('login'))
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        return $next($request);
    }
}
