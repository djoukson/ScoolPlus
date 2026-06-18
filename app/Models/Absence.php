<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'inscription_id',
        'matiere_id',
        'decoupage_id',
        'heures',
        'is_justified',
    ];

    protected $casts = [
        'is_justified' => 'boolean',
        'heures' => 'float',
    ];

    /* ================= RELATIONS ================= */

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    public function decoupage()
    {
        return $this->belongsTo(Decoupage::class);
    }
}
