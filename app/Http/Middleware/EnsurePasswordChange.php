<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('settings.index', 'settings.updatePassword', 'logoutt')) {
            return $next($request);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'securite'])
            ->with('force_password_change', true);
    }
}
