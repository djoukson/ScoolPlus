<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaireRemuneration extends Model
{
    protected $fillable = [
        'personnel_id',
        'enseignant_id',
        'type',
        'nom',
        'prenom',
        'salaire',
        'date_debut',
        'date_fin',
    ];

    // Relation vers Personnel
    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    // Relation vers Enseignant
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}
