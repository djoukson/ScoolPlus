<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Eleve extends Model
{
    use HasFactory;

    protected $table = 'eleves';
	protected $fillable = [
		'user_id',
		'matricule',
		'nom',
		'prenom',
		'date_naissance',
		'sexe',
		'adresse',
		'tuteur_nom',
		'tuteur_tel',
		'lieudenaissance',
		'nationalite',
		'observation',
		'imglink',
		'statut',
		'taille_eleve',
		'pointure_chaussure',
		'taille_habit'
	];

    protected $casts = [
        'date_naissance' => 'date', // ✅ permet d'utiliser ->format()
    ];
	public function user()
	{
		return $this->belongsTo(User::class);
	}

    // ✅ Un élève appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    // ✅ Un élève peut avoir plusieurs inscriptions
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'eleve_id');
    }
    public function classeActuelle()
    {
        return $this->hasOne(Inscription::class, 'eleve_id')->latestOfMany();
    }
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'eleve_id');
    }
    // Paiements pour l'inscription actuelle
    public function paiementsClasseActuelle()
    {
        $classeId = $this->classeActuelle?->classe_id;
        $anneeId  = $this->classeActuelle?->annee_id;

        return $this->paiements()
            ->where('classe_id', $classeId)
            ->where('annee_id', $anneeId);
    }
    public function parent()
    {
        return $this->hasOne(ParentEleve::class, 'eleve_id');
    }
    public function souscriptions()
    {
        return $this->hasMany(\App\Models\Souscription::class, 'eleve_id');
    }
    public function bourses()
    {
        return $this->hasManyThrough(
            Bourse::class,            // Modèle final
            AttributionBourse::class, // Modèle intermédiaire
            'inscription_id',         // Clé étrangère dans attributions
            'id',                     // Clé locale dans bourses
            'id',                     // Clé locale dans eleves
            'bourse_id'               // Clé étrangère dans attributions vers bourses
        );
    }

    public function attributions()
    {
        return $this->hasManyThrough(
            \App\Models\AttributionBourse::class,
            \App\Models\Inscription::class,
            'eleve_id',       // Clé étrangère dans inscriptions
            'inscription_id', // Clé étrangère dans attributions
            'id',             // Clé locale dans eleves
            'id'              // Clé locale dans inscriptions
        );
    }

    public function absences()
    {
        return $this->hasManyThrough(
            Absence::class,
            Inscription::class
        );
    }
    // Toutes les notes via les inscriptions
    public function notes()
    {
        return $this->hasManyThrough(
            Evaluation::class,      // Modèle Note
            Inscription::class,
            'eleve_id',       // clé étrangère dans inscriptions
            'inscription_id', // clé étrangère dans notes
            'id',             // clé locale eleve
            'id'              // clé locale inscription
        );
    }
    public function moyennes()
    {
        return $this->hasManyThrough(
            Moyenne::class,
            Inscription::class,
            'eleve_id', // clé étrangère dans inscriptions
            'inscription_id', // clé étrangère dans moyennes
            'id', // clé locale dans eleves
            'id'  // clé locale dans inscriptions
        )->with('decoupage.annee'); // pour récupérer l'année scolaire via le découpage
    }

public function parents()
{
    return $this->belongsToMany(
        User::class,
        'parent_eleve_user',
        'eleve_id',
        'user_id'
    )->withPivot('relation')
     ->withTimestamps();
}


public function conversations()
{
    return $this->hasMany(Conversation::class);
}
}
