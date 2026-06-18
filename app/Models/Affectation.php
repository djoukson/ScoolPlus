<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Affectation
 *
 * @property int $id
 * @property int $enseignant_id
 * @property int $classe_id
 * @property int $matiere_id
 * @property int $annee_id
 * @property int|null $heures_attribuees
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property AnneesScolaire $annees_scolaire
 * @property Classe $class
 * @property Enseignant $enseignant
 * @property Matiere $matiere
 *
 * @package App\Models
 */
class Affectation extends Model
{
	protected $table = 'affectations';

	protected $casts = [
		'enseignant_id' => 'int',
		'classe_id' => 'int',
		'matiere_id' => 'int',
		'annee_id' => 'int',
		'heures_attribuees' => 'int'
	];

	protected $fillable = [
		'enseignant_id',
		'classe_id',
		'matiere_id',
		'annee_id',
		'heures_attribuees'
	];

	public function annees_scolaire()
	{
		return $this->belongsTo(AnneesScolaire::class, 'annee_id');
	}

	public function classe()
	{
		return $this->belongsTo(Classe::class, 'classe_id');
	}

	public function enseignant()
	{
		return $this->belongsTo(Enseignant::class);
	}

	public function matiere()
	{
		return $this->belongsTo(Matiere::class);
	}
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'classe_id');
    }
    public function emploisDuTemps()
    {
        return $this->hasMany(EmploiDuTemps::class, 'affectation_id');
    }

}
