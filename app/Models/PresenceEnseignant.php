<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresenceEnseignant extends Model
{
    protected $table = 'presences_enseignants';

    protected $fillable = [
        'enseignant_id',
        'date',
        'nombre_heures',
        'motif',
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }
}
