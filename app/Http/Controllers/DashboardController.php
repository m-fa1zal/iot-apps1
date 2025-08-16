<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        // Get basic stats for the old dashboard
        $totalDevices = Device::where('station_active', true)->count();
        $onlineDevices = Device::where('station_active', true)->where('status', 'online')->count();
        $offlineDevices = Device::where('station_active', true)->where('status', 'offline')->count();
        
        return view('dashboard', compact('totalDevices', 'onlineDevices', 'offlineDevices'));
    }

    /**
     * Display the real-time dashboard
     */
    public function realtime()
    {
        $user = auth()->user();
        $states = \App\Models\State::orderBy('name')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        
        return view('dashboard.index', compact('user', 'states', 'districts'));
    }

    /**
     * Get dashboard data (AJAX)
     */
    public function getData()
    {
        try {
            // Get all active devices with their latest readings
            $devices = Device::with(['state', 'district'])
                ->where('station_active', true)
                ->get();

            // Calculate summary statistics
            $summary = [
                'total' => $devices->count(),
                'online' => $devices->where('status', 'online')->count(),
                'offline' => $devices->where('status', 'offline')->count(),
                'maintenance' => $devices->where('status', 'maintenance')->count(),
            ];

            // Get latest readings for all devices in one query (avoid N+1)
            $latestReadings = SensorReading::select('device_id', 'temperature', 'humidity', 'battery_voltage', 'rssi', 'reading_time')
                ->whereIn('device_id', $devices->pluck('id'))
                ->whereIn('id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('sensor_readings')
                        ->groupBy('device_id');
                })
                ->get()
                ->keyBy('device_id');

            // Get stations with their latest readings
            $stations = $devices->map(function ($device) use ($latestReadings) {
                // Get latest sensor reading from pre-loaded data
                $latestReading = $latestReadings->get($device->id);

                // Last seen as fixed timestamp (not relative counting time)
                $lastSeenFormatted = null;
                if ($latestReading && $latestReading->reading_time) {
                    // Show fixed timestamp format instead of relative time that keeps counting
                    // Add timezone to ensure consistency
                    $lastSeenFormatted = $latestReading->reading_time->setTimezone('Asia/Singapore')->format('M d, Y H:i');
                } else {
                    $lastSeenFormatted = 'No data';
                }

                return [
                    'id' => $device->id,
                    'station_id' => $device->station_id,
                    'station_name' => $device->station_name,
                    'status' => $device->status,
                    'last_seen_formatted' => $lastSeenFormatted,
                    'state' => $device->state,
                    'district' => $device->district,
                    'latest_reading' => $latestReading ? [
                        'temperature' => number_format($latestReading->temperature, 1),
                        'humidity' => number_format($latestReading->humidity, 1),
                        'battery_voltage' => number_format($latestReading->battery_voltage, 2),
                        'rssi' => $latestReading->rssi,
                        'reading_time' => $latestReading->reading_time->format('Y-m-d H:i:s'),
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'stations' => $stations,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single station details (AJAX)
     */
    public function getStationDetails($id)
    {
        try {
            $device = Device::with(['state', 'district'])
                ->where('id', $id)
                ->where('station_active', true)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found',
                ], 404);
            }

            // Get latest sensor reading
            $latestReading = SensorReading::where('device_id', $device->id)
                ->orderBy('reading_time', 'desc')
                ->first();

            // Format device data
            $stationData = [
                'id' => $device->id,
                'station_id' => $device->station_id,
                'station_name' => $device->station_name,
                'status' => $device->status,
                'mac_address' => $device->mac_address,
                'full_location' => $device->getFullLocationAttribute(),
                'last_seen_formatted' => $device->last_seen ? $device->last_seen->format('M d, Y H:i') : null,
                'latest_reading' => $latestReading ? [
                    'temperature' => number_format($latestReading->temperature, 1),
                    'humidity' => number_format($latestReading->humidity, 1),
                    'battery_voltage' => number_format($latestReading->battery_voltage, 2),
                    'rssi' => $latestReading->rssi,
                    'reading_time' => $latestReading->reading_time->format('Y-m-d H:i:s'),
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'station' => $stationData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load station details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request latest update from ESP32 (AJAX)
     */
    public function requestUpdate($id)
    {
        try {
            // Debug log for CSRF token issue
            \Log::info('Request Update called', [
                'device_id' => $id,
                'request_headers' => request()->headers->all(),
                'csrf_token' => request()->header('X-CSRF-TOKEN'),
                'session_token' => session()->token()
            ]);
            $device = Device::where('id', $id)
                ->where('station_active', true)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found',
                ], 404);
            }

            // In a real implementation, this would trigger the ESP32 to send new data
            // For now, we'll simulate by creating a web-triggered sensor reading
            
            // Check if device has recent readings to simulate with
            $lastReading = SensorReading::where('device_id', $device->id)
                ->orderBy('reading_time', 'desc')
                ->first();

            if ($lastReading) {
                // Create a new "web-triggered" reading with slight variations
                $newReading = SensorReading::create([
                    'device_id' => $device->id,
                    'temperature' => $lastReading->temperature + (rand(-20, 20) / 10), // ±2°C variation
                    'humidity' => max(0, min(100, $lastReading->humidity + (rand(-50, 50) / 10))), // ±5% variation
                    'rssi' => $lastReading->rssi + rand(-5, 5), // ±5 dBm variation
                    'battery_voltage' => max(0, $lastReading->battery_voltage + (rand(-10, 10) / 100)), // ±0.1V variation
                    'reading_time' => now(),
                    'web_triggered' => true,
                ]);

                // Update device last_seen and request_update status
                $device->update([
                    'last_seen' => now(),
                    'request_update' => true
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Request Data From Remote Station',
                    'reading' => [
                        'temperature' => number_format($newReading->temperature, 1),
                        'humidity' => number_format($newReading->humidity, 1),
                        'battery_voltage' => number_format($newReading->battery_voltage, 2),
                        'rssi' => $newReading->rssi,
                        'reading_time' => $newReading->reading_time->format('Y-m-d H:i:s'),
                    ],
                ]);
            } else {
                // No previous readings, create initial simulated data
                $newReading = SensorReading::create([
                    'device_id' => $device->id,
                    'temperature' => rand(200, 350) / 10, // 20-35°C
                    'humidity' => rand(300, 800) / 10, // 30-80%
                    'rssi' => rand(-100, -60), // -100 to -60 dBm
                    'battery_voltage' => rand(320, 420) / 100, // 3.2-4.2V
                    'reading_time' => now(),
                    'web_triggered' => true,
                ]);

                // Update device last_seen and request_update status
                $device->update([
                    'last_seen' => now(),
                    'request_update' => true
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Request Data From Remote Station',
                    'reading' => [
                        'temperature' => number_format($newReading->temperature, 1),
                        'humidity' => number_format($newReading->humidity, 1),
                        'battery_voltage' => number_format($newReading->battery_voltage, 2),
                        'rssi' => $newReading->rssi,
                        'reading_time' => $newReading->reading_time->format('Y-m-d H:i:s'),
                    ],
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get historical data for station (AJAX)
     */
    public function getHistoricalData($id, Request $request)
    {
        try {
            $device = Device::where('id', $id)
                ->where('station_active', true)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found',
                ], 404);
            }

            $query = SensorReading::where('device_id', $device->id);

            // Apply date filters
            if ($request->filled('start_date')) {
                $query->whereDate('reading_time', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('reading_time', '<=', $request->end_date);
            }

            // If no filters, default to today
            if (!$request->filled('start_date') && !$request->filled('end_date')) {
                $query->whereDate('reading_time', today());
            }

            $readings = $query->orderBy('reading_time', 'asc')
                ->limit(200) // Limit for chart performance
                ->get();

            $formattedReadings = $readings->map(function ($reading) {
                return [
                    'reading_time' => $reading->reading_time->toISOString(),
                    'temperature' => (float) $reading->temperature,
                    'humidity' => (float) $reading->humidity,
                    'battery_voltage' => (float) $reading->battery_voltage,
                    'rssi' => (int) $reading->rssi,
                ];
            });

            return response()->json([
                'success' => true,
                'readings' => $formattedReadings,
                'total' => $readings->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load historical data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simulate real-time data generation for testing
     */
    public function simulateData()
    {
        try {
            $devices = Device::where('station_active', true)->get();

            foreach ($devices as $device) {
                // Get last reading for this device
                $lastReading = SensorReading::where('device_id', $device->id)
                    ->orderBy('reading_time', 'desc')
                    ->first();

                if ($lastReading) {
                    // Create variation based on last reading
                    $temperature = $lastReading->temperature + (rand(-20, 20) / 10);
                    $humidity = max(0, min(100, $lastReading->humidity + (rand(-50, 50) / 10)));
                    $batteryVoltage = max(0, $lastReading->battery_voltage + (rand(-5, 5) / 100));
                    $rssi = $lastReading->rssi + rand(-3, 3);
                } else {
                    // Generate initial random data
                    $temperature = rand(200, 350) / 10; // 20-35°C
                    $humidity = rand(300, 800) / 10; // 30-80%
                    $batteryVoltage = rand(320, 420) / 100; // 3.2-4.2V
                    $rssi = rand(-100, -60); // -100 to -60 dBm
                }

                // Create new reading
                SensorReading::create([
                    'device_id' => $device->id,
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'rssi' => $rssi,
                    'battery_voltage' => $batteryVoltage,
                    'reading_time' => now(),
                    'web_triggered' => false,
                ]);

                // Update device status and last_seen
                $device->update([
                    'last_seen' => now(),
                    'status' => 'online'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Simulated data generated for ' . $devices->count() . ' devices',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to simulate data: ' . $e->getMessage(),
            ], 500);
        }
    }
}