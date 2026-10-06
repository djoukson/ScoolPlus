<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class RecoverAdminPassword extends Command
{
    protected $signature = 'app:recover-admin {identifier : Exact admin email, username, or matricule}';

    protected $description = 'Recover one admin account from the local application console';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('identifier'));

        $admins = User::query()
            ->where('role', 'admin')
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier)
                    ->orWhere('username', $identifier)
                    ->orWhere('matricule', $identifier);
            })
            ->get();

        if ($admins->count() !== 1) {
            $this->error('L’identifiant doit correspondre à un seul compte admin.');
            return self::FAILURE;
        }

        $admin = $admins->first();

        $this->warn('Compte ciblé : '.$admin->name.' ('.$admin->matricule.')');
        $confirmation = 'RECUPERER '.$admin->matricule;

        if ($this->ask('Pour confirmer, saisissez : '.$confirmation) !== $confirmation) {
            $this->error('Confirmation incorrecte. Aucune modification effectuée.');
            return self::FAILURE;
        }

        if (!isset($admin->session_version)) {
            $this->error('La migration de session_version doit être appliquée avant la récupération.');
            return self::FAILURE;
        }

        $temporaryPassword = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        $admin->forceFill([
            'password' => Hash::make($temporaryPassword),
            'status' => true,
            'must_change_password' => true,
            'remember_token' => Str::random(60),
            'session_version' => ((int) $admin->session_version) + 1,
        ])->save();

        RateLimiter::clear('login-failures:user:'.$admin->id);

        Log::notice('Admin password recovered through local console.', [
            'user_id' => $admin->id,
            'matricule' => $admin->matricule,
        ]);

        $this->newLine();
        $this->info('Récupération terminée. Le compte est réactivé et les anciennes sessions seront refusées.');
        $this->line('Mot de passe temporaire (à communiquer à l’admin, affiché une seule fois) :');
        $this->line($temporaryPassword);
        $this->warn('À la connexion, il devra immédiatement choisir un nouveau mot de passe.');

        return self::SUCCESS;
    }
}
