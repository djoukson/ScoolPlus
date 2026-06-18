<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Enseignant
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $matricule
 * @property string $nom
 * @property string $prenom
 * @property string|null $specialite
 * @property string|null $tel
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User|null $user
 * @property Collection|Affectation[] $affectations
 * @property Collection|Classe[] $classes
 * @property Collection|Emploi[] $emplois
 *
 * @package App\Models
 */
class Enseignant extends Model
{
	protected $table = 'enseignants';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'matricule',
		'nom',
		'prenom',
		'specialite',
		'tel',
		'email',
		'type',
		'statut',
		'groupesanguin',
		'adresse',
		'sexe',
		'niveau_id',
		'salaire_mensuel'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function affectations()
	{
		return $this->hasMany(Affectation::class);
	}

	public function classes()
	{
		return $this->hasMany(Classe::class);
	}

	public function emplois()
	{
		return $this->hasMany(EmploiDuTemps::class);
	}
    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id');
    }
    public function salaires()
    {
        return $this->hasMany(SalaireMois::class, 'user_id')->where('user_type', 'enseignant');
    }
}
