<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_name',
        'station_id',
        'api_token',
        'mac_address',
        'data_interval_minutes',
        'data_collection_time_minutes',
        'state_id',
        'district_id',
        'address',
        'gps_latitude',
        'gps_longitude',
        'status',
        'station_active',
        'request_update',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'status' => 'string',
        'station_active' => 'boolean',
        'request_update' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($device) {
            // Auto-generate API token
            if (empty($device->api_token)) {
                $device->api_token = Str::random(64);
            }
            
            // Auto-generate station_id based on district code
            if (empty($device->station_id) && $device->district_id) {
                $district = District::find($device->district_id);
                if ($district && $district->district_code) {
                    // Get the count of existing devices in this district
                    $existingCount = static::where('district_id', $device->district_id)->count();
                    
                    // Generate station_id: DISTRICT_CODE-1001, 1002, etc.
                    $sequenceNumber = 1001 + $existingCount;
                    $device->station_id = $district->district_code . '-' . $sequenceNumber;
                }
            }
        });
    }


    /**
     * Get the state for the device
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the district for the device
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get sensor readings for this device
     */
    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    /**
     * Check if device is online
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }
        
        return $this->last_seen->gt(now()->subMinutes(5));
    }

    /**
     * Get device status badge
     */
    public function getStatusBadge(): string
    {
        return match($this->status) {
            'online' => '<span class="badge bg-success">Online</span>',
            'offline' => '<span class="badge bg-danger">Offline</span>',
            'maintenance' => '<span class="badge bg-warning">Maintenance</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get full location string
     */
    public function getFullLocationAttribute(): string
    {
        $location = [];
        
        if ($this->state) {
            $location[] = $this->state->name;
        }
        
        if ($this->district) {
            $location[] = $this->district->name;
        }
        
        if ($this->address) {
            $location[] = $this->address;
        }
        
        return implode(', ', $location);
    }

    /**
     * Regenerate API token
     */
    public function regenerateApiToken(): string
    {
        $this->api_token = Str::random(64);
        $this->save();
        
        return $this->api_token;
    }

    /**
     * Generate station_id based on district
     */
    public function generateStationId(): string
    {
        if ($this->district && $this->district->district_code) {
            // Get the count of existing devices in this district (excluding current device)
            $existingCount = static::where('district_id', $this->district_id)
                                  ->where('id', '!=', $this->id)
                                  ->count();
            
            // Generate station_id: DISTRICT_CODE-1001, 1002, etc.
            $sequenceNumber = 1001 + $existingCount;
            $this->station_id = $this->district->district_code . '-' . $sequenceNumber;
            $this->save();
            
            return $this->station_id;
        }
        
        return '';
    }

    /**
     * Update device status based on last activity
     */
    public function updateStatus(): void
    {
        if ($this->isOnline()) {
            $this->status = 'online';
        } else {
            $this->status = 'offline';
        }
        
        $this->save();
    }
}
