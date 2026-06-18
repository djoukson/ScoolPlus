<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $table = 'licenses';

    protected $fillable = [
        'school_name',
        'license_key',
        'duration_years',
        'expires_at',
        'machine_id',
        'status',
    ];

    protected $dates = [
        'expires_at',
        'created_at',
        'updated_at'
    ];

    // Vérifie si la licence est expirée
    public function isExpired()
    {
        if ($this->expires_at === null) return false; // Lifetime
        return now()->gt($this->expires_at);
    }

    // Vérifie si la licence est valide
    public function isValid()
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
