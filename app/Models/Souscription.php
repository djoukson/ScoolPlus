<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Souscription extends Model
{
    use HasFactory;
    protected $table = 'souscriptions';
    protected $fillable = ['eleve_id', 'service_id','annee_id'];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class,'eleve_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class,'service_id');
    }

    public function annee()
    {
        return $this->belongsTo(AnneesScolaire::class,'annee_id');
    }
}
