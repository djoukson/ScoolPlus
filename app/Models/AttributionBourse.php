<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributionBourse extends Model
{
    use HasFactory;
protected $table = 'attributions';
    protected $fillable = ['inscription_id', 'bourse_id', 'date_attribution','etat'];

    public function bourse()
    {
        return $this->belongsTo(Bourse::class);
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }
    // Méthode pour vérifier si la bourse est active
    public function isActive()
    {
        return $this->etat === 'active';
    }
}
