<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decoupage extends Model
{
    protected $table = 'decoupages';

    protected $fillable = [
        'nom',
        'type',
        'annee_id',
    ];

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }
    public function epreuves()
    {
        return $this->hasMany(Epreuve::class, 'decoupage_id');
    }
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
