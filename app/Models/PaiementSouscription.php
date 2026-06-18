<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementSouscription extends Model
{
    use HasFactory;
    protected $table = 'paiement_souscription';
    protected $fillable = [
        'souscription_id',
        'eleve_id',
        'service_id',
        'montant',
        'date_paiement'
    ];

    public function souscription()
    {
        return $this->belongsTo(Souscription::class, 'souscription_id');
    }

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

}
