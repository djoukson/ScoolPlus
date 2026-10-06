<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'nom',
        'niveau_id',
        'enseignant_id',
        'annee_id',
        'type_decoupage',
        'decoupage_id',
    ];

    // ✅ Une classe appartient à un enseignant si cest niveau primaire
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }

    // ✅ Une classe appartient à une année scolaire
    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

    // ✅ Une classe a plusieurs inscriptions
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'classe_id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'classe_id');
    }
    public function cours()
    {
        return $this->hasMany(Cour::class, 'classe_id');
    }
    public function emplois()
    {
        return $this->hasMany(\App\Models\EmploiDuTemps::class, 'classe_id');
    }
    public function montantsFrais()
    {
        return $this->hasMany(MontantFrais::class, 'classe_id', 'id');
    }
    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id');
    }
    public function decoupage()
    {
        return $this->belongsTo(Decoupage::class, 'decoupage_id');
    }
    public function titulaire()
    {
        $annee_id = session('annee_id') ?? AnneesScolaire::where('active', 1)->value('id');

        return $this->hasOne(Titulaire::class)
            ->where('annee_id', $annee_id)
            ->with('enseignant');
    }
    // ✅ Une classe a plusieurs élèves (VIA inscriptions)
    public function eleves()
    {
        return $this->hasManyThrough(
            Eleve::class,       // modèle final
            Inscription::class, // modèle intermédiaire
            'classe_id',        // FK sur inscriptions
            'id',               // PK sur eleves
            'id',               // PK sur classes
            'eleve_id'          // FK sur inscriptions vers eleves
        );
    }


}
