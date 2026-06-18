<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploiDuTemps extends Model
{
    use HasFactory;
protected $table = 'emplois_du_temps';
    protected $fillable = [
        'classe_id',
        'heure_cours_id',
        'affectation_id',
        'jour'
    ];


    public function heure()
    {
        return $this->belongsTo(HeureCours::class, 'heure_cours_id');
    }

    public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }
    public function classe()
    {
        return $this->belongsTo(\App\Models\Classe::class, 'classe_id');
    }
    // 🔹 Relation avec la matière
    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'matiere_id');
    }
}

