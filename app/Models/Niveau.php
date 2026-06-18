<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Niveau extends Model
{
    protected $fillable = ['nom'];

    public function enseignants(): Collection
    {
        return $this->hasMany(Enseignant::class, 'niveau_id');
    }
    public function tauxHoraire()
    {
        return $this->hasOne(TauxHoraire::class);
    }

}
