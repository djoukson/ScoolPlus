<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Titulaire extends Model
{
    use HasFactory;

    protected $fillable = ['enseignant_id', 'classe_id', 'annee_id'];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class, 'annee_id');
    }

//    public function titulaire()
//    {
//        $annee_id = session('annee_id') ?? AnneesScolaire::where('active', 1)->value('id');
//
//        return $this->hasOne(Titulaire::class)
//            ->where('annee_id', $annee_id)
//            ->with('enseignant');
//    }
}
