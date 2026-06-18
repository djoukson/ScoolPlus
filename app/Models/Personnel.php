<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    protected $fillable = [
        'nom',
        'prenom',
        'poste',
        'salaire',
        'tel',
        'email',
        'statut',
        'groupesanguin',
        'adresse',
        'sexe',
    ];
    public function salaires()
    {
        return $this->hasMany(SalaireMois::class, 'user_id')->where('user_type', 'personnel');
    }
}
