<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MontantScolarite extends Model
{
    protected $table = 'montants_scolarite';

    protected $fillable = [
        'montant',
        'description',
    ];
}
