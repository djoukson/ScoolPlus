<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moyenne extends Model
{
    protected $table = 'moyennes';

    protected $fillable = [
        'inscription_id',
        'decoupage_id',
        'moyenne',
        'rang',
        'appreciation',

        'moyenne_annuelle',
        'rang_annuel',           // ajouté
        'appreciation_annuelle', // ajouté
    ];

    // 🔗 Relations
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function decoupage()
    {
        return $this->belongsTo(Decoupage::class);
    }
}
