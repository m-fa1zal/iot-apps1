<?php

namespace App\Exports;

use App\Models\SensorReading;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SensorReadingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $deviceId;
    protected $startDate;
    protected $endDate;

    public function __construct($deviceId, $startDate = null, $endDate = null)
    {
        $this->deviceId = $deviceId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = SensorReading::with('device')
            ->where('device_id', $this->deviceId);

        if ($this->startDate) {
            $query->whereDate('reading_time', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('reading_time', '<=', $this->endDate);
        }

        return $query->orderBy('reading_time', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Station ID',
            'Station Name',
            'Reading Time',
            'Temperature (°C)',
            'Humidity (%)',
            'RSSI (dBm)',
            'Battery Voltage (V)',
            'Type',
            'Created At',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->device->station_id ?? 'N/A',
            $row->device->station_name ?? 'N/A',
            $row->reading_time ? $row->reading_time->format('Y-m-d H:i:s') : 'N/A',
            $row->temperature,
            $row->humidity,
            $row->rssi,
            $row->battery_voltage,
            $row->web_triggered ? 'Manual' : 'Scheduled',
            $row->created_at->format('Y-m-d H:i:s'),
        ];
    }
}