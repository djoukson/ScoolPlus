<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['libelle', 'description', 'montant', 'annee_id'];

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

    public function souscriptions()
    {
        return $this->hasMany(Souscription::class);
    }
}
