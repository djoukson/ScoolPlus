<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'montant',
        'date_depense',
    ];
    protected $casts = [
        'date_depense' => 'date',
    ];
}
