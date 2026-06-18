<?php

namespace App\Listeners;
namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\UserLog;

class LogUserLogin
{
    public function handle(Login $event)
    {
        UserLog::create([
            'user_id' => $event->user->id,
            'action' => 'Connexion',
            'description' => 'L’utilisateur s’est connecté au système.',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
