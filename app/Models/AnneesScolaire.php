<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AnneesScolaire
 *
 * @property int $id
 * @property string $nom
 * @property Carbon $date_debut
 * @property Carbon $date_fin
 * @property bool|null $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Affectation[] $affectations
 * @property Collection|Classe[] $classes
 * @property Collection|Inscription[] $inscriptions
 *
 * @package App\Models
 */
class AnneesScolaire extends Model
{
	protected $table = 'annees_scolaires';

	protected $casts = [
		'date_debut' => 'datetime',
		'date_fin' => 'datetime',
		'active' => 'bool'
	];

	protected $fillable = [
		'nom',
		'date_debut',
		'date_fin',
		'active'
	];

	public function affectations()
	{
		return $this->hasMany(Affectation::class, 'annee_id');
	}

	public function classes()
	{
		return $this->hasMany(Classe::class, 'annee_id');
	}

	public function inscriptions()
	{
		return $this->hasMany(Inscription::class, 'annee_id');
	}


    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
