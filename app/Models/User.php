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
    'matricule',
    'username',
    'sexe',
    'name',
    'email',
    'password',
    'role',
    'phone',
    'profileimg',
    'adresse',
    'groupesanguin',
    'status',
    'must_change_password',
    'remember_token',
    'session_version',
];
    protected $casts = [
        'status' => 'boolean',
        'must_change_password' => 'boolean',
        'session_version' => 'integer',
    ];
  protected $hidden = [
        'password',
        'remember_token',
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
    $lastMatricule = User::where('matricule', 'like', $prefix . '%')
        ->orderByRaw("CAST(SUBSTRING(matricule, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
        ->value('matricule');

    if ($lastMatricule) {
        $number = intval(substr($lastMatricule, strlen($prefix))) + 1;
    } else {
        $number = 1;
    }

    if ($number > 9999) {
        throw new \Exception('Limite de matricules atteinte');
    }

    return $prefix . str_pad($number, 2, '0', STR_PAD_LEFT);
}

public function enfants()
{
    return $this->belongsToMany(
        Eleve::class,
        'parent_eleve_user',
        'user_id',
        'eleve_id'
    )
    ->withPivot('relation')
    ->withTimestamps();
}

public function conversations()
{
    return $this->belongsToMany(
        Conversation::class,
        'conversation_participants'
    )
    ->withPivot('last_read_at')
    ->withTimestamps();
}

public function messages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

}
