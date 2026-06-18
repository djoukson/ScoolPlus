<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table = 'evaluations';

    protected $fillable = [
        'inscription_id',
        'matiere_id',
        'decoupage_id',
        'type_evaluation_id',
        'note',
        'observation',
        'date_eval',
    ];

    // 🔗 Relations
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    public function decoupage()
    {
        return $this->belongsTo(Decoupage::class);
    }

    public function typeEvaluation()
    {
        return $this->belongsTo(TypeEvaluation::class, 'type_evaluation_id');
    }
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }
}
