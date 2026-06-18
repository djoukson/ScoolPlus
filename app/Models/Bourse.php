<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bourse extends Model
{
    use HasFactory;
    protected $table = 'bourses';
    protected $fillable = ['nom', 'description'];

    public function frais()
    {
        // Relation Many-to-Many avec champ pivot 'pourcentage'
        return $this->belongsToMany(Frais::class, 'bourse_frais')
            ->withPivot('pourcentage')
            ->withTimestamps();
    }

    public function attributions()
    {
        return $this->hasMany(AttributionBourse::class);
    }

    protected static function booted()
    {
        static::deleting(function ($bourse) {
            // Supprime toutes les attributions liées
            $bourse->attributions()->delete();

            // Supprime les entrées dans la table pivot bourse_frais
            $bourse->frais()->detach();
        });
    }
}


