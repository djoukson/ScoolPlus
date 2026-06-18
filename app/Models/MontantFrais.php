<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MontantFrais extends Model
{
    use HasFactory;

    protected $table = 'montants_frais';

    protected $fillable = ['annee_id', 'classe_id', 'frais_id', 'montant'];

    public function frais()
    {
        return $this->belongsTo(Frais::class);
    }

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}
