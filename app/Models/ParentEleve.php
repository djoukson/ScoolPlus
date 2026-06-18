<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentEleve extends Model
{
    use HasFactory;

    protected $table = 'parent_eleves';

    protected $fillable = [
        'eleve_id',
        'pere_nom', 'pere_tel', 'pere_profession',
        'pere_email', 'pere_adresse',
        'mere_nom', 'mere_tel', 'mere_profession',
        'mere_email', 'mere_adresse',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}
