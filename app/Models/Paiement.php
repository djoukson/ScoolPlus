<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;
protected $table = 'paiements';
    protected $fillable = [
        'eleve_id',
        'annee_id',
        'classe_id',
        'frais_id',
        'montant_paye',
        'date_paiement',
        'mode_paiement'
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function frais()
    {
        return $this->belongsTo(Frais::class);
    }
    // 🔹 Relation vers l'inscription
    public function inscription()
    {
        return $this->belongsTo(Inscription::class, 'eleve_id', 'eleve_id')
            ->whereColumn('classe_id', 'paiements.classe_id')
            ->whereColumn('annee_id', 'paiements.annee_id');
    }
}
