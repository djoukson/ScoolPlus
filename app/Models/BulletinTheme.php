<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulletinTheme extends Model
{
    protected $fillable = [
        'nom',
        'blade',
        'active',
    ];
}

