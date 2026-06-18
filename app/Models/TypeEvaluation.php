<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeEvaluation extends Model
{
    protected $table = 'types_evaluations';

    protected $fillable = [
        'nom',
        'nom_normalized',
    ];
}
