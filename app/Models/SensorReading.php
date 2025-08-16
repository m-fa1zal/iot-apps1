<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'rssi',
        'battery_voltage',
        'reading_time',
        'web_triggered'
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'battery_voltage' => 'decimal:2',
        'reading_time' => 'datetime',
        'web_triggered' => 'boolean'
    ];

    /**
     * Get the device that owns the sensor reading
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get battery level status
     */
    public function getBatteryLevelAttribute(): string
    {
        if (!$this->battery_voltage) return 'Unknown';
        
        $voltage = (float) $this->battery_voltage;
        if ($voltage >= 3.7) return 'Full';
        if ($voltage >= 3.3) return 'Medium';
        return 'Low';
    }

    /**
     * Get signal strength status
     */
    public function getSignalStrengthAttribute(): string
    {
        if (!$this->rssi) return 'Unknown';
        
        $rssi = (int) $this->rssi;
        if ($rssi >= -70) return 'Excellent';
        if ($rssi >= -85) return 'Good';
        if ($rssi >= -100) return 'Fair';
        return 'Poor';
    }

    /**
     * Get formatted temperature
     */
    public function getFormattedTemperatureAttribute(): string
    {
        return $this->temperature ? number_format($this->temperature, 1) . '°C' : 'N/A';
    }

    /**
     * Get formatted humidity
     */
    public function getFormattedHumidityAttribute(): string
    {
        return $this->humidity ? number_format($this->humidity, 1) . '%' : 'N/A';
    }

    /**
     * Get formatted battery voltage
     */
    public function getFormattedBatteryVoltageAttribute(): string
    {
        return $this->battery_voltage ? number_format($this->battery_voltage, 2) . 'V' : 'N/A';
    }

    /**
     * Get formatted RSSI
     */
    public function getFormattedRssiAttribute(): string
    {
        return $this->rssi ? $this->rssi . ' dBm' : 'N/A';
    }
}
