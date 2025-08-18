<?php

namespace App\Http\Controllers;

use App\Models\StationInformation;
use App\Models\SensorReading;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Display the dashboard
     */
    public function index()
    {
        // Get basic stats for the old dashboard
        $totalStations = StationInformation::where('station_active', true)->count();
        
        // Get station status from device_status table
        $onlineStations = DB::table('station_information as si')
            ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
            ->where('si.station_active', true)
            ->where('ds.status', 'online')
            ->count();
            
        $offlineStations = DB::table('station_information as si')
            ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
            ->where('si.station_active', true)
            ->where(function($query) {
                $query->where('ds.status', 'offline')
                      ->orWhereNull('ds.status');
            })
            ->count();
        
        return view('dashboard', compact('totalStations', 'onlineStations', 'offlineStations'));
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
            // Get all active stations with their status and latest readings
            $stations = DB::table('station_information as si')
                ->leftJoin('states as s', 'si.state_id', '=', 's.id')
                ->leftJoin('districts as d', 'si.district_id', '=', 'd.id')
                ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
                ->where('si.station_active', true)
                ->select(
                    'si.id',
                    'si.station_id',
                    'si.station_name',
                    'si.state_id',
                    'si.district_id',
                    'si.gps_latitude',
                    'si.gps_longitude',
                    's.name as state_name',
                    'd.name as district_name',
                    'ds.status',
                    'ds.last_seen'
                )
                ->get();

            // Calculate summary statistics
            $summary = [
                'total' => $stations->count(),
                'online' => $stations->where('status', 'online')->count(),
                'offline' => $stations->where('status', 'offline')->count() + $stations->whereNull('status')->count(),
                'maintenance' => $stations->where('status', 'maintenance')->count(),
            ];

            // Get latest readings for all stations in one query (avoid N+1)
            $latestReadings = DB::table('sensor_readings')
                ->select('station_id', 'temperature', 'humidity', 'battery_voltage', 'rssi', 'reading_time')
                ->whereIn('station_id', $stations->pluck('station_id'))
                ->whereIn('id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('sensor_readings')
                        ->groupBy('station_id');
                })
                ->get()
                ->keyBy('station_id');

            // Format stations data
            $stationsData = $stations->map(function ($station) use ($latestReadings) {
                // Get latest sensor reading from pre-loaded data
                $latestReading = $latestReadings->get($station->station_id);

                // Last seen as fixed timestamp (not relative counting time)
                $lastSeenFormatted = null;
                if ($station->last_seen) {
                    $lastSeenFormatted = Carbon::parse($station->last_seen)->format('M d, Y H:i');
                } elseif ($latestReading && $latestReading->reading_time) {
                    $lastSeenFormatted = Carbon::parse($latestReading->reading_time)->format('M d, Y H:i');
                } else {
                    $lastSeenFormatted = 'No data';
                }

                return [
                    'id' => $station->id,
                    'station_id' => $station->station_id,
                    'station_name' => $station->station_name,
                    'status' => $station->status ?? 'offline',
                    'last_seen_formatted' => $lastSeenFormatted,
                    'state_name' => $station->state_name,
                    'district_name' => $station->district_name,
                    'state_id' => $station->state_id,
                    'district_id' => $station->district_id,
                    'gps_latitude' => $station->gps_latitude,
                    'gps_longitude' => $station->gps_longitude,
                    'latest_reading' => $latestReading ? [
                        'temperature' => $latestReading->temperature ? number_format($latestReading->temperature, 1) : null,
                        'humidity' => $latestReading->humidity ? number_format($latestReading->humidity, 1) : null,
                        'battery_voltage' => $latestReading->battery_voltage ? number_format($latestReading->battery_voltage, 2) : null,
                        'rssi' => $latestReading->rssi,
                        'reading_time' => $latestReading->reading_time,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'stations' => $stationsData,
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
            $station = DB::table('station_information as si')
                ->leftJoin('states as s', 'si.state_id', '=', 's.id')
                ->leftJoin('districts as d', 'si.district_id', '=', 'd.id')
                ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->where('si.id', $id)
                ->where('si.station_active', true)
                ->select(
                    'si.*',
                    's.name as state_name',
                    'd.name as district_name',
                    'ds.status',
                    'ds.last_seen',
                    'dc.mac_address'
                )
                ->first();

            if (!$station) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found',
                ], 404);
            }

            // Get latest sensor reading
            $latestReading = DB::table('sensor_readings')
                ->where('station_id', $station->station_id)
                ->orderBy('reading_time', 'desc')
                ->first();

            // Format station data
            $stationData = [
                'id' => $station->id,
                'station_id' => $station->station_id,
                'station_name' => $station->station_name,
                'status' => $station->status ?? 'offline',
                'mac_address' => $station->mac_address,
                'full_location' => $station->state_name . ', ' . $station->district_name,
                'gps_latitude' => $station->gps_latitude,
                'gps_longitude' => $station->gps_longitude,
                'address' => $station->address,
                'last_seen_formatted' => $station->last_seen ? Carbon::parse($station->last_seen)->format('M d, Y H:i') : null,
                'latest_reading' => $latestReading ? [
                    'temperature' => $latestReading->temperature ? number_format($latestReading->temperature, 1) : null,
                    'humidity' => $latestReading->humidity ? number_format($latestReading->humidity, 1) : null,
                    'battery_voltage' => $latestReading->battery_voltage ? number_format($latestReading->battery_voltage, 2) : null,
                    'rssi' => $latestReading->rssi,
                    'reading_time' => $latestReading->reading_time,
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
     * Request latest update from ESP32 via MQTT (AJAX)
     */
    public function requestUpdate($id)
    {
        try {
            \Log::info('MQTT Request Update called', [
                'station_id' => $id,
                'csrf_token' => request()->header('X-CSRF-TOKEN'),
                'session_token' => session()->token()
            ]);

            // Get station information with device configuration
            $station = DB::table('station_information as si')
                ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
                ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
                ->where('si.id', $id)
                ->where('si.station_active', true)
                ->select('si.*', 'ds.status', 'ds.last_seen', 'ds.request_update', 'dc.data_interval')
                ->first();

            if (!$station) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found',
                ], 404);
            }

            // Set request_update flag - device will get this on next heartbeat (regardless of online status)
            DB::table('device_status')
                ->where('station_id', $station->station_id)
                ->update(['request_update' => true, 'updated_at' => now()]);
            
            \Log::info('Data request queued via updateRequest flag', [
                'station_db_id' => $station->id,
                'station_id' => $station->station_id,
                'requested_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data request sent successfully',
                'data_interval' => $station->data_interval ?? 5, // Default to 5 minutes if not set
                'station_id' => $station->station_id,
                'station_name' => $station->station_name
            ]);

        } catch (\Exception $e) {
            \Log::error('MQTT Dashboard Request Update Error', [
                'error' => $e->getMessage(),
                'station_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send MQTT request to station',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get historical data for station (AJAX)
     */
    public function getHistoricalData($id, Request $request)
    {
        try {
            // Get station information
            $station = DB::table('station_information')
                ->where('id', $id)
                ->where('station_active', true)
                ->first();

            if (!$station) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found',
                ], 404);
            }

            $query = DB::table('sensor_readings')->where('station_id', $station->station_id);

            // Apply date filters - using from_date and to_date to match frontend
            if ($request->filled('from_date')) {
                $query->whereDate('reading_time', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('reading_time', '<=', $request->to_date);
            }

            // If no filters, default to today
            if (!$request->filled('from_date') && !$request->filled('to_date')) {
                $query->whereDate('reading_time', today());
            }

            $readings = $query->orderBy('reading_time', 'asc')
                ->limit(200) // Limit for chart performance
                ->get();

            $formattedReadings = $readings->map(function ($reading) {
                return [
                    'reading_time' => \Carbon\Carbon::parse($reading->reading_time)->toISOString(),
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
            $stations = DB::table('station_information')
                ->where('station_active', true)
                ->get();

            foreach ($stations as $station) {
                // Get last reading for this station
                $lastReading = DB::table('sensor_readings')
                    ->where('station_id', $station->station_id)
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
                DB::table('sensor_readings')->insert([
                    'station_id' => $station->station_id,
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'rssi' => $rssi,
                    'battery_voltage' => $batteryVoltage,
                    'reading_time' => now(),
                    'web_triggered' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update device status and last_seen
                DB::table('device_status')
                    ->where('station_id', $station->station_id)
                    ->update([
                        'last_seen' => now(),
                        'status' => 'online',
                        'updated_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Simulated data generated for ' . $stations->count() . ' stations',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to simulate data: ' . $e->getMessage(),
            ], 500);
        }
    }
}