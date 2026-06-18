<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cour
 *
 * @property int $id
 * @property int $classe_id
 * @property int $matiere_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Classe $class
 * @property Matiere $matiere
 * @property Collection|Emploi[] $emplois
 *
 * @package App\Models
 */
class Cour extends Model
{
	protected $table = 'cours';

	protected $casts = [
		'classe_id' => 'int',
		'matiere_id' => 'int'
	];

	protected $fillable = [
		'classe_id',
		'matiere_id'
	];

	public function class()
	{
		return $this->belongsTo(Classe::class, 'classe_id');
	}

	public function matiere()
	{
		return $this->belongsTo(Matiere::class);
	}

	public function emplois()
	{
		return $this->hasMany(Emploi::class, 'cours_id');
	}
}
