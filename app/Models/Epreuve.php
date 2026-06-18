<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Epreuve extends Model
{
    protected $fillable = [
        'nom_fichier',
        'chemin_fichier',
        'uploaded_by',
        'classe_id',
        'matiere_id',
        'type_evaluation_id',
        'annee_id',
        'decoupage_id',
        'etat',



    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'matiere_id');
    }

    public function typeEvaluation()
    {
        return $this->belongsTo(TypeEvaluation::class, 'type_evaluation_id');
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
