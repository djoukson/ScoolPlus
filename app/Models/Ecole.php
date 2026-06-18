<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ecole extends Model
{
    protected $table = 'ecole';

    protected $fillable = [
        'nom', 'adresse', 'telephone', 'email',
        'directeur', 'directeur_primaire','annee_id', 'site_web', 'logo'
    ];

    // 🔗 Relation avec Année scolaire
    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

}
