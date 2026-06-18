<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Note
 * 
 * @property int $id
 * @property int $inscription_id
 * @property int $matiere_id
 * @property string $type_eval
 * @property float $note
 * @property Carbon $date_eval
 * @property string|null $trimestre
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Inscription $inscription
 * @property Matiere $matiere
 *
 * @package App\Models
 */
class Note extends Model
{
	protected $table = 'notes';

	protected $casts = [
		'inscription_id' => 'int',
		'matiere_id' => 'int',
		'note' => 'float',
		'date_eval' => 'datetime'
	];

	protected $fillable = [
		'inscription_id',
		'matiere_id',
		'type_eval',
		'note',
		'date_eval',
		'trimestre'
	];

	public function inscription()
	{
		return $this->belongsTo(Inscription::class);
	}

	public function matiere()
	{
		return $this->belongsTo(Matiere::class);
	}
}
