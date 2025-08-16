<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\State;
use App\Models\District;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SensorReadingsExport;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Device::with(['state', 'district'])
                      ->where('station_active', true);

        // Filter by state
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        // Filter by district
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Search by station name or station id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('station_name', 'like', "%{$search}%")
                  ->orWhere('station_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(15);
        $states = State::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

        return view('devices.index', compact('devices', 'states', 'districts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $states = State::orderBy('name')->get();
        return view('devices.create', compact('states'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'mac_address' => 'required|string|max:17',
            'data_interval_minutes' => 'required|integer|min:1|max:1440',
            'data_collection_time_minutes' => 'required|integer|min:1|max:60',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $device = Device::create([
            'station_name' => $request->station_name,
            'state_id' => $request->state_id,
            'district_id' => $request->district_id,
            'address' => $request->address,
            'gps_latitude' => $request->gps_latitude,
            'gps_longitude' => $request->gps_longitude,
            'mac_address' => $request->mac_address,
            'data_interval_minutes' => $request->data_interval_minutes,
            'data_collection_time_minutes' => $request->data_collection_time_minutes,
            'status' => 'offline', // Default status
            'station_active' => true, // Default active
        ]);

        return redirect()->route('devices.index')
            ->with('success', 'Device created successfully! Station ID: ' . $device->station_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        $device->load(['state', 'district']);
        return view('devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        $states = State::orderBy('name')->get();
        $districts = District::where('state_id', $device->state_id)->orderBy('name')->get();
        
        return view('devices.edit', compact('device', 'states', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $validator = Validator::make($request->all(), [
            'station_name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string|max:255',
            'status' => 'required|in:online,offline,maintenance',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if district changed, regenerate station_id if needed
        $regenerateStationId = $device->district_id != $request->district_id;

        $device->update([
            'station_name' => $request->station_name,
            'state_id' => $request->state_id,
            'district_id' => $request->district_id,
            'address' => $request->address,
            'telegram_chat_id' => $request->telegram_chat_id,
            'status' => $request->status,
        ]);

        // Regenerate station_id if district changed
        if ($regenerateStationId) {
            $device->generateStationId();
        }

        return redirect()->route('devices.index')
            ->with('success', 'Device updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $device->update(['station_active' => false]);

        return redirect()->route('devices.index')
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
     * Update station information
     */
    public function updateInfo(Request $request, Device $device)
    {
        $validator = Validator::make($request->all(), [
            'station_name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:online,offline,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $device->update([
            'station_name' => $request->station_name,
            'state_id' => $request->state_id,
            'district_id' => $request->district_id,
            'address' => $request->address,
            'gps_latitude' => $request->gps_latitude,
            'gps_longitude' => $request->gps_longitude,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Station information updated successfully!'
        ]);
    }

    /**
     * Update device configuration
     */
    public function updateConfig(Request $request, Device $device)
    {
        $validator = Validator::make($request->all(), [
            'mac_address' => 'required|string|max:17',
            'data_interval_minutes' => 'required|integer|min:1|max:1440',
            'data_collection_time_minutes' => 'required|integer|min:1|max:60',
            'api_token' => 'required|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $device->update([
            'mac_address' => $request->mac_address,
            'data_interval_minutes' => $request->data_interval_minutes,
            'data_collection_time_minutes' => $request->data_collection_time_minutes,
            'api_token' => $request->api_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device configuration updated successfully!'
        ]);
    }

    /**
     * Generate new API token for device (AJAX)
     */
    public function generateApiToken(Device $device)
    {
        $newToken = $device->regenerateApiToken();

        return response()->json([
            'success' => true,
            'token' => $newToken,
            'message' => 'New API token generated successfully!'
        ]);
    }

    /**
     * Regenerate API token for device
     */
    public function regenerateToken(Device $device)
    {
        $newToken = $device->regenerateApiToken();

        return redirect()->back()
            ->with('success', 'API token regenerated successfully!');
    }

    /**
     * Get historical sensor data for device (AJAX)
     */
    public function getHistoricalData(Request $request, Device $device)
    {
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

        $readings = $query->orderBy('reading_time', 'desc')
                         ->limit(1000) // Limit to prevent memory issues
                         ->get();

        $formattedData = $readings->map(function ($reading) {
            return [
                'reading_time' => $reading->reading_time ? $reading->reading_time->format('Y-m-d H:i:s') : 'N/A',
                'temperature' => $reading->temperature ? number_format($reading->temperature, 2) : 'N/A',
                'humidity' => $reading->humidity ? number_format($reading->humidity, 2) : 'N/A',
                'rssi' => $reading->rssi ?? 'N/A',
                'battery_voltage' => $reading->battery_voltage ? number_format($reading->battery_voltage, 2) : 'N/A',
                'type' => $reading->web_triggered ? 'Manual' : 'Scheduled',
                'created_at' => $reading->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'total' => $readings->count()
        ]);
    }

    /**
     * Export historical data to Excel
     */
    public function exportHistoricalData(Request $request, Device $device)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        $filename = 'sensor_data_' . $device->station_id . '_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new SensorReadingsExport($device->id, $startDate, $endDate), $filename);
    }
}
