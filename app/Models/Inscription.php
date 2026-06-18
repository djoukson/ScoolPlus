<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $table = 'inscriptions';

    protected $fillable = [
        'eleve_id',
        'classe_id',
        'annee_id',
        'date_inscription',
        'status_eleve',
    ];

    protected $casts = [
        'date_inscription' => 'date',
    ];

    // 🔗 Relation avec Élève
    public function eleve()
    {
        return $this->belongsTo(Eleve::class, 'eleve_id');
    }

    // 🔗 Relation avec Classe
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    // 🔗 Relation avec Année scolaire
    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

    public function totalAbsenceHeures($decoupageId = null)
    {
        return $this->absences()
            ->when($decoupageId, fn($q) =>
            $q->where('decoupage_id', $decoupageId)
            )
            ->sum('heures');
    }
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
    public function attributions()
    {
        return $this->hasManyThrough(
            \App\Models\AttributionBourse::class, // Modèle final
            \App\Models\Inscription::class,       // Modèle intermédiaire ? Ici déjà Inscription, donc on fait direct
            'eleve_id',   // Clé étrangère dans Inscription vers Eleve
            'inscription_id', // Clé étrangère dans AttributionBourse
            'id',         // Clé locale dans Inscription
            'id'          // Clé locale dans AttributionBourse
        );
    }
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'inscription_id', 'id');
    }


}
