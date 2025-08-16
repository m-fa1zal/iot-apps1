<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
        'code'
    ];

    /**
     * Get the state that owns the district
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get devices in this district
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
