<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaireMois extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',   // 'enseignant' ou 'personnel'
        'user_id',     // id de l'enseignant ou du personnel
        'annee',       // année du salaire
        'mois',        // numéro du mois (1-12)
        'heures',      // total d’heures (pour college/lycée)
        'taux_horaire', // taux horaire applicable
        'salaire',     // salaire calculé ou fixe pour primaire/personnel
        'periode',     // format YYYY-MM
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'user_id')->where('user_type', 'enseignant');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'user_id')->where('user_type', 'personnel');
    }
}
