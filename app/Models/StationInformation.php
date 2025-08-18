<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationInformation extends Model
{
    use HasFactory;

    protected $table = 'station_information';

    protected $fillable = [
        'station_name',
        'station_id',
        'state_id',
        'district_id',
        'address',
        'gps_latitude',
        'gps_longitude',
        'station_active',
    ];

    protected $casts = [
        'station_active' => 'boolean',
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
    ];

    /**
     * Get the state that owns the station.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the district that owns the station.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the sensor readings for the station.
     */
    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class, 'station_id', 'station_id');
    }
}