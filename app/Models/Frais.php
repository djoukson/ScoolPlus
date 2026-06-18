<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frais extends Model
{
    use HasFactory;

    protected $fillable = ['libelle', 'description'];

    public function montants()
    {
        return $this->hasMany(MontantFrais::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
    public function bourses()
    {
        return $this->belongsToMany(Bourse::class, 'bourse_frais')
            ->withPivot('pourcentage')
            ->withTimestamps();
    }
}
