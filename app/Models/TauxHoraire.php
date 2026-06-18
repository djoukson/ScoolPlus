<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TauxHoraire extends Model
{
    use HasFactory;

    protected $fillable = ['niveau_id', 'taux'];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }
}
