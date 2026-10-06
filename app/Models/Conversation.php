<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'eleve_id',
        'created_by',
        'type',
        'subject',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(
            User::class,
            'conversation_participants'
        )
        ->withPivot('last_read_at')
        ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}