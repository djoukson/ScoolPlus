<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoyenneGenerale extends Model
{
    protected $table = 'moyennes_generales';

    protected $fillable = [
        'classe_id',
        'annee_id',
        'decoupage_id',
        'moyenne_classe',
        'moyenne_forte',
        'moyenne_faible',
    ];

    // Relations
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

    public function decoupage()
    {
        return $this->belongsTo(Decoupage::class, 'decoupage_id');
    }
}
