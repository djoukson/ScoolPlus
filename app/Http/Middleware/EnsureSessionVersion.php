<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->status) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('danger', 'Ce compte est désactivé.');
        }

        $sessionVersion = $request->session()->get('auth_session_version');
        if ($sessionVersion !== null && (int) $sessionVersion === (int) $user->session_version) {
            return $next($request);
        }

        if ($sessionVersion === null && Auth::viaRemember()) {
            $request->session()->put('auth_session_version', (int) $user->session_version);
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'danger',
            'Votre session a expiré. Veuillez vous reconnecter.'
        );
    }
}
