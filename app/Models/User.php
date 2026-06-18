<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'matricule',
        'email',
        'password',
        'role',
        'phone',
        'username',
        'sexe',
        'profileimg',
        'status',
    ];

    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }

    public function epreuves()
    {
        return $this->hasMany(Epreuve::class, 'uploaded_by');
    }

    public static function generateMatricule($prefix = 'ENS')
    {
        // Récupère le dernier matricule ENS
        $lastMatricule = User::where('matricule', 'like', $prefix.'%')
            ->orderBy('matricule', 'desc')
            ->value('matricule');

        if ($lastMatricule) {
            // Extraire les 2 derniers chiffres
            $number = intval(substr($lastMatricule, -2)) + 1;
        } else {
            $number = 1;
        }

        // Limite à 99 (optionnel)
        if ($number > 99) {
            throw new \Exception('Limite de matricules atteinte');
        }

        return $prefix . str_pad($number, 2, '0', STR_PAD_LEFT);
    }

}
