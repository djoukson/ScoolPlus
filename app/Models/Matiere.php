<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Matiere
 *
 * @property int $id
 * @property string $nom
 * @property string $niveau
 * @property int|null $coefficient
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Affectation[] $affectations
 * @property Collection|Cour[] $cours
 * @property Collection|Note[] $notes
 *
 * @package App\Models
 */
class Matiere extends Model
{
	protected $table = 'matieres';

    protected $casts = [
        'coefficient' => 'int',
        'cout_par_heure' => 'decimal:2',
    ];

	protected $fillable = [
		'nom',
		'niveau_id',
		'coefficient',
        'sigle'
	];

	public function affectations()
	{
		return $this->hasMany(Affectation::class);
	}

	public function cours()
	{
		return $this->hasMany(Cour::class);
	}

	public function notes()
	{
		return $this->hasMany(Note::class);
	}
    public function enseignant()
    {
        return $this->hasOneThrough(
            Enseignant::class,   // Modèle final
            Affectation::class,  // Table intermédiaire
            'matiere_id',        // Foreign key sur affectations
            'id',                // Foreign key sur enseignants
            'id',                // Local key sur matières
            'enseignant_id'      // Local key sur affectations
        );
    }
    // Relations
    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id');
    }
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
