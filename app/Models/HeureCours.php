<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeureCours extends Model
{
    use HasFactory;
    protected $table = 'heures_cours';
    protected $fillable = ['libelle', 'heure_debut', 'heure_fin'];

    public function emplois()
    {
        return $this->hasMany(EmploiDuTemps::class);
    }
}

