<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\State;
use App\Models\District;

class StationController extends Controller
{
    /**
     * Display a listing of stations
     */
    public function index(Request $request)
    {
        $query = DB::table('station_information as si')
            ->leftJoin('states as s', 'si.state_id', '=', 's.id')
            ->leftJoin('districts as d', 'si.district_id', '=', 'd.id')
            ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
            ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
            ->select(
                'si.*',
                's.name as state_name',
                'd.name as district_name',
                'dc.mac_address',
                'dc.data_interval',
                'dc.data_collection_time',
                'dc.configuration_update',
                'ds.status',
                'ds.last_seen',
                'ds.request_update'
            )
            ->where('si.station_active', true);

        // Filter by state
        if ($request->filled('state_id')) {
            $query->where('si.state_id', $request->state_id);
        }

        // Filter by district
        if ($request->filled('district_id')) {
            $query->where('si.district_id', $request->district_id);
        }

        // Search by station name or station id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('si.station_name', 'like', "%{$search}%")
                  ->orWhere('si.station_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('ds.status', $request->status);
        }

        $stations = $query->orderBy('si.created_at', 'desc')->paginate(15);
        $states = State::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

        return view('stations.index', compact('stations', 'states', 'districts'));
    }

    /**
     * Show the form for creating a new station
     */
    public function create()
    {
        $states = State::orderBy('name')->get();
        return view('stations.create', compact('states'));
    }

    /**
     * Store a newly created station
     */
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'station_name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string|max:500',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'mac_address' => 'required|string|max:17|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'data_interval' => 'required|integer|min:1|max:60',
            'data_collection_time' => 'required|integer|min:10|max:300',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Set database timeout to prevent 504 errors
        DB::statement('SET SESSION wait_timeout = 30');
        DB::statement('SET SESSION innodb_lock_wait_timeout = 30');

        DB::beginTransaction();
        try {
            // Validate district exists and has proper setup
            $district = DB::table('districts')->where('id', $request->district_id)->first();
            if (!$district) {
                throw new \Exception('Selected district not found');
            }

            // Generate unique station_id with timeout protection
            $stationId = $this->generateStationId($request->district_id);

            // Validate MAC address uniqueness
            $existingMac = DB::table('device_configurations')
                ->where('mac_address', $request->mac_address)
                ->exists();
            
            if ($existingMac) {
                throw new \Exception('MAC address already exists in the system');
            }

            // Create station information
            $stationInserted = DB::table('station_information')->insert([
                'station_name' => trim($request->station_name),
                'station_id' => $stationId,
                'state_id' => $request->state_id,
                'district_id' => $request->district_id,
                'address' => trim($request->address),
                'gps_latitude' => $request->gps_latitude,
                'gps_longitude' => $request->gps_longitude,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$stationInserted) {
                throw new \Exception('Failed to create station information');
            }

            // Create device configuration
            $configInserted = DB::table('device_configurations')->insert([
                'station_id' => $stationId,
                'api_token' => \Illuminate\Support\Str::random(64),
                'mac_address' => strtoupper($request->mac_address),
                'data_interval' => $request->data_interval,
                'data_collection_time' => $request->data_collection_time,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$configInserted) {
                throw new \Exception('Failed to create device configuration');
            }

            // Create device status
            $statusInserted = DB::table('device_status')->insert([
                'station_id' => $stationId,
                'status' => 'offline',
                'request_update' => false,
                'last_seen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$statusInserted) {
                throw new \Exception('Failed to create device status');
            }

            DB::commit();

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Station created successfully! Station ID: ' . $stationId,
                    'station_id' => $stationId
                ]);
            }

            return redirect()->route('stations.index')
                ->with('success', 'Station created successfully! Station ID: ' . $stationId);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Log the error for debugging
            \Log::error('Station creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
            ]);

            // Check if request expects JSON (AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create station: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Failed to create station: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified station
     */
    public function show(Request $request, $stationId)
    {
        $station = DB::table('station_information as si')
            ->leftJoin('states as s', 'si.state_id', '=', 's.id')
            ->leftJoin('districts as d', 'si.district_id', '=', 'd.id')
            ->leftJoin('device_configurations as dc', 'si.station_id', '=', 'dc.station_id')
            ->leftJoin('device_status as ds', 'si.station_id', '=', 'ds.station_id')
            ->select(
                'si.*',
                's.name as state_name',
                'd.name as district_name',
                'dc.api_token',
                'dc.mac_address',
                'dc.data_interval',
                'dc.data_collection_time',
                'dc.configuration_update',
                'ds.status',
                'ds.last_seen',
                'ds.request_update'
            )
            ->where('si.station_id', $stationId)
            ->first();

        if (!$station) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Station not found'], 404);
            }
            abort(404, 'Station not found');
        }

        // If it's an AJAX request (for modal), return JSON
        if ($request->expectsJson()) {
            return response()->json($station);
        }

        // Get recent sensor readings
        $recentReadings = DB::table('sensor_readings')
            ->where('station_id', $stationId)
            ->orderBy('reading_time', 'desc')
            ->limit(10)
            ->get();

        // Get MQTT task logs
        $mqttLogs = DB::table('mqtt_task_logs')
            ->where('station_id', $stationId)
            ->orderBy('received_at', 'desc')
            ->limit(20)
            ->get();

        return view('stations.show', compact('station', 'recentReadings', 'mqttLogs'));
    }

    /**
     * Update station information
     */
    public function updateInfo(Request $request, $stationId)
    {
        $validator = Validator::make($request->all(), [
            'station_name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if station exists
            $station = DB::table('station_information')->where('station_id', $stationId)->first();
            if (!$station) {
                return response()->json([
                    'success' => false,
                    'message' => 'Station not found'
                ], 404);
            }

            $updated = DB::table('station_information')
                ->where('station_id', $stationId)
                ->update([
                    'station_name' => $request->station_name,
                    'state_id' => $request->state_id,
                    'district_id' => $request->district_id,
                    'address' => $request->address,
                    'gps_latitude' => $request->gps_latitude,
                    'gps_longitude' => $request->gps_longitude,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes were made or station not found'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Station information updated successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Station update error: ' . $e->getMessage(), [
                'station_id' => $stationId,
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update device configuration
     */
    public function updateConfig(Request $request, $stationId)
    {
        $validator = Validator::make($request->all(), [
            'mac_address' => 'required|string|max:17',
            'data_interval' => 'required|integer|min:1|max:60',
            'data_collection_time' => 'required|integer|min:1|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::table('device_configurations')
            ->where('station_id', $stationId)
            ->update([
                'mac_address' => $request->mac_address,
                'data_interval' => $request->data_interval,
                'data_collection_time' => $request->data_collection_time,
                'configuration_update' => true, // Mark for ESP32 to pickup
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Device configuration updated successfully!'
        ]);
    }

    /**
     * Generate new API token for station
     */
    public function regenerateToken($stationId)
    {
        $newToken = \Illuminate\Support\Str::random(64);

        DB::table('device_configurations')
            ->where('station_id', $stationId)
            ->update([
                'api_token' => $newToken,
                'configuration_update' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'token' => $newToken,
            'message' => 'New API token generated successfully!'
        ]);
    }

    /**
     * Get historical sensor data for station
     */
    public function getHistoricalData(Request $request, $stationId)
    {
        $query = DB::table('sensor_readings')->where('station_id', $stationId);

        // Apply date filters
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

        // Get total count for pagination
        $totalCount = $query->count();
        
        // Apply pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $offset = ($page - 1) * $perPage;
        
        $readings = $query->orderBy('reading_time', 'desc')
                         ->offset($offset)
                         ->limit($perPage)
                         ->get();

        $formattedData = $readings->map(function ($reading) {
            return [
                'reading_time' => $reading->reading_time ?? 'N/A',
                'station_id' => $reading->station_id ?? 'N/A',
                'temperature' => $reading->temperature ? number_format($reading->temperature, 2) : 'N/A',
                'humidity' => $reading->humidity ? number_format($reading->humidity, 2) : 'N/A',
                'rssi' => $reading->rssi ?? 'N/A',
                'battery_voltage' => $reading->battery_voltage ? number_format($reading->battery_voltage, 2) : 'N/A',
                'type' => $reading->web_triggered ? 'Manual' : 'Scheduled',
                'created_at' => $reading->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'total' => $totalCount,
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'last_page' => ceil($totalCount / $perPage)
        ]);
    }

    /**
     * Get MQTT task logs for station
     */
    public function getTaskLogs(Request $request, $stationId)
    {
        $query = DB::table('mqtt_task_logs')->where('station_id', $stationId);

        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('received_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('received_at', '<=', $request->to_date);
        }

        // If no filters, default to today
        if (!$request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('received_at', today());
        }

        // Get total count for pagination
        $totalCount = $query->count();
        
        // Apply pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $offset = ($page - 1) * $perPage;
        
        $logs = $query->orderBy('received_at', 'desc')
                     ->offset($offset)
                     ->limit($perPage)
                     ->get();

        $formattedData = $logs->map(function ($log) {
            return [
                'received_at' => $log->received_at ?? 'N/A',
                'station_id' => $log->station_id ?? 'N/A',
                'task_type' => $log->task_type ?? 'N/A',
                'status' => $log->status ?? 'N/A',
                'message' => $log->topic ?? 'N/A',
                'response_time_ms' => $log->response_time_ms ?? null,
                'direction' => $log->direction ?? 'N/A',
                'created_at' => $log->created_at ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'total' => $totalCount,
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'last_page' => ceil($totalCount / $perPage)
        ]);
    }

    /**
     * Deactivate station (soft delete)
     */
    public function destroy($stationId)
    {
        DB::table('station_information')
            ->where('station_id', $stationId)
            ->update([
                'station_active' => false,
                'updated_at' => now(),
            ]);

        return redirect()->route('stations.index')
            ->with('success', 'Station deactivated successfully!');
    }

    /**
     * Get districts by state (for AJAX)
     */
    public function getDistricts(Request $request)
    {
        $districts = District::where('state_id', $request->state_id)
                           ->orderBy('name')
                           ->get(['id', 'name', 'district_code']);

        return response()->json($districts);
    }

    /**
     * Validate MAC address uniqueness
     */
    public function validateMacAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mac_address' => 'required|string|max:17',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'error' => 'Invalid MAC address format'
            ], 422);
        }

        try {
            // Check if MAC address already exists
            $exists = DB::table('device_configurations')
                ->where('mac_address', strtoupper($request->mac_address))
                ->exists();

            return response()->json([
                'available' => !$exists,
                'message' => $exists ? 'MAC address already exists' : 'MAC address is available'
            ]);

        } catch (\Exception $e) {
            \Log::error('MAC address validation error: ' . $e->getMessage());
            
            return response()->json([
                'available' => false,
                'error' => 'Unable to validate MAC address'
            ], 500);
        }
    }

    /**
     * Export historical sensor data for station as CSV
     */
    public function exportData(Request $request, $stationId)
    {
        try {
            // Get station information
            $station = DB::table('station_information')
                ->where('station_id', $stationId)
                ->where('station_active', true)
                ->first();

            if (!$station) {
                abort(404, 'Station not found');
            }

            $query = DB::table('sensor_readings')->where('station_id', $stationId);

            // Apply date filters
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

            $readings = $query->orderBy('reading_time', 'desc')->get();

            // Generate CSV content
            $csvData = [];
            $csvData[] = [
                'Reading Time',
                'Station ID',
                'Temperature (°C)',
                'Humidity (%)',
                'RSSI (dBm)',
                'Battery Voltage (V)',
                'Type'
            ];

            foreach ($readings as $reading) {
                $csvData[] = [
                    $reading->reading_time ?? 'N/A',
                    $reading->station_id ?? 'N/A',
                    $reading->temperature ? number_format($reading->temperature, 2) : 'N/A',
                    $reading->humidity ? number_format($reading->humidity, 2) : 'N/A',
                    $reading->rssi ?? 'N/A',
                    $reading->battery_voltage ? number_format($reading->battery_voltage, 2) : 'N/A',
                    $reading->web_triggered ? 'Manual' : 'Scheduled'
                ];
            }

            // Create CSV filename
            $dateRange = '';
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $dateRange = "_{$request->from_date}_to_{$request->to_date}";
            } elseif ($request->filled('from_date')) {
                $dateRange = "_from_{$request->from_date}";
            } elseif ($request->filled('to_date')) {
                $dateRange = "_to_{$request->to_date}";
            } else {
                $dateRange = "_" . today()->format('Y-m-d');
            }

            $filename = "station_{$stationId}_data{$dateRange}.csv";

            // Set headers for CSV download
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            // Create CSV content
            $callback = function() use ($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate unique station ID based on district
     */
    private function generateStationId($districtId)
    {
        $district = District::find($districtId);
        if (!$district) {
            throw new \Exception('Invalid district selected');
        }

        // Use district code if available, otherwise generate from district name
        $districtCode = $district->district_code ?? $district->code ?? strtoupper(substr($district->name, 0, 3));
        
        if (empty($districtCode)) {
            throw new \Exception('Unable to generate district code for station ID');
        }

        // Get count of existing stations in this district
        $existingCount = DB::table('station_information')
            ->where('district_id', $districtId)
            ->count();

        // Generate station_id: DISTRICT_CODE + sequential number
        $sequenceNumber = str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);
        return $districtCode . $sequenceNumber;
    }
}