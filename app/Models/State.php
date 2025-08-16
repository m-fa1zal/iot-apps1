<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    /**
     * Get the districts for the state
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get devices in this state
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
