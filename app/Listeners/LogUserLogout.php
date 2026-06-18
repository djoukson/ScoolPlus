<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\UserLog;

class LogUserLogout
{
    public function handle(Logout $event)
    {
        UserLog::create([
            'user_id' => $event->user->id,
            'action' => 'Déconnexion',
            'description' => 'L’utilisateur s’est déconnecté du système.',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

