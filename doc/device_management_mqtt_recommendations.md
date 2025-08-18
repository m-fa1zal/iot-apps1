# Device Management Recommendations for MQTT Integration

## Overview
This document outlines recommended changes to the device management system to fully leverage MQTT capabilities and improve real-time communication with ESP32 devices.

## Current Device Management Analysis

### Existing Components:
- **DeviceController**: Web-based device CRUD operations
- **DashboardController**: Real-time dashboard with manual data requests
- **Device Model**: Database representation with `request_update` flag
- **Legacy REST API**: HTTP-based communication (now deprecated)

### Current Limitations:
1. **Manual Data Requests**: Dashboard still uses HTTP simulation for "Request Data"
2. **Limited Real-time Capabilities**: No immediate device status updates
3. **Connection Status**: Basic online/offline tracking without real-time heartbeat
4. **Device Commands**: Limited to data requests only

## Recommended MQTT Enhancements

### 1. Real-time Device Status Management

#### Add MQTT Heartbeat System
```php
// Add to Device migration
Schema::table('devices', function (Blueprint $table) {
    $table->timestamp('mqtt_last_seen')->nullable();
    $table->boolean('mqtt_connected')->default(false);
    $table->integer('mqtt_heartbeat_interval')->default(60); // seconds
    $table->json('mqtt_subscriptions')->nullable(); // track subscribed topics
});
```

#### Update Device Model
```php
// app/Models/Device.php
class Device extends Model 
{
    protected $fillable = [
        // ... existing fields
        'mqtt_last_seen',
        'mqtt_connected',
        'mqtt_heartbeat_interval',
        'mqtt_subscriptions'
    ];

    protected $casts = [
        'mqtt_subscriptions' => 'array',
        'mqtt_last_seen' => 'datetime',
        'mqtt_connected' => 'boolean'
    ];

    public function isMqttOnline(): bool
    {
        if (!$this->mqtt_connected || !$this->mqtt_last_seen) {
            return false;
        }
        
        $threshold = now()->subSeconds($this->mqtt_heartbeat_interval * 2);
        return $this->mqtt_last_seen->isAfter($threshold);
    }
}
```

### 2. Enhanced Dashboard Controller

#### Replace HTTP Simulation with Real MQTT Commands
```php
// app/Http/Controllers/DashboardController.php
use App\Services\MqttService;

class DashboardController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Request data from device via MQTT (replace current simulation)
     */
    public function requestUpdate(Request $request)
    {
        $device = Device::findOrFail($request->device_id);
        
        if (!$device->isMqttOnline()) {
            return response()->json([
                'success' => false,
                'error' => 'Device is not connected to MQTT'
            ], 422);
        }

        // Send actual MQTT command instead of simulation
        $command = [
            'command' => 'request_data',
            'station_id' => $device->station_id,
            'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
            'priority' => 'high'
        ];

        $success = $this->mqttService->publish(
            "iot/commands/{$device->station_id}/request_data", 
            json_encode($command)
        );

        if ($success) {
            $device->update(['request_update' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Data request sent to device via MQTT',
                'mqtt_connected' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to send MQTT command'
        ], 500);
    }
}
```

### 3. Device Command Management

#### Create Device Command Controller
```php
// app/Http/Controllers/DeviceCommandController.php
<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\MqttService;
use Illuminate\Http\Request;

class DeviceCommandController extends Controller
{
    private $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Send configuration update to device
     */
    public function updateConfig(Request $request, Device $device)
    {
        $validated = $request->validate([
            'data_interval_minutes' => 'integer|min:1|max:1440',
            'data_collection_time_minutes' => 'integer|min:1|max:60'
        ]);

        if (!$device->isMqttOnline()) {
            return response()->json([
                'success' => false,
                'error' => 'Device is not connected to MQTT'
            ], 422);
        }

        // Update device settings
        $device->update($validated);

        // Send new config via MQTT
        $config = [
            'serverTime' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
            'updateRequest' => false,
            'nextCheckInterval' => $device->data_interval_minutes * 60,
            'station_id' => $device->station_id,
            'data_collection_time' => $device->data_collection_time_minutes * 60,
        ];

        $success = $this->mqttService->sendConfigToDevice($config);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Configuration sent to device' : 'Failed to send configuration',
            'config' => $config
        ]);
    }

    /**
     * Send device reboot command
     */
    public function rebootDevice(Device $device)
    {
        if (!$device->isMqttOnline()) {
            return response()->json([
                'success' => false,
                'error' => 'Device is not connected to MQTT'
            ], 422);
        }

        $command = [
            'command' => 'reboot',
            'station_id' => $device->station_id,
            'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
        ];

        $success = $this->mqttService->publish(
            "iot/commands/{$device->station_id}/reboot",
            json_encode($command)
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Reboot command sent to device' : 'Failed to send reboot command'
        ]);
    }

    /**
     * Update device firmware (if supported)
     */
    public function updateFirmware(Device $device, Request $request)
    {
        $validated = $request->validate([
            'firmware_url' => 'required|url',
            'firmware_version' => 'required|string|max:50'
        ]);

        if (!$device->isMqttOnline()) {
            return response()->json([
                'success' => false,
                'error' => 'Device is not connected to MQTT'
            ], 422);
        }

        $command = [
            'command' => 'update_firmware',
            'station_id' => $device->station_id,
            'firmware_url' => $validated['firmware_url'],
            'firmware_version' => $validated['firmware_version'],
            'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
        ];

        $success = $this->mqttService->publish(
            "iot/commands/{$device->station_id}/firmware_update",
            json_encode($command)
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Firmware update command sent' : 'Failed to send firmware update command'
        ]);
    }
}
```

### 4. Enhanced MQTT Service

#### Add Device-Specific Methods
```php
// app/Services/MqttService.php - Add these methods

/**
 * Subscribe to device-specific status updates
 */
public function subscribeToDeviceStatus(callable $callback): bool
{
    return $this->subscribe('iot/devices/+/status', $callback);
}

/**
 * Subscribe to device heartbeat
 */
public function subscribeToDeviceHeartbeat(callable $callback): bool
{
    return $this->subscribe('iot/devices/+/heartbeat', $callback);
}

/**
 * Send command to specific device
 */
public function sendDeviceCommand(string $stationId, string $command, array $data = []): bool
{
    $topic = "iot/commands/{$stationId}/{$command}";
    $message = json_encode(array_merge($data, [
        'command' => $command,
        'station_id' => $stationId,
        'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
    ]));
    
    return $this->publish($topic, $message);
}

/**
 * Subscribe to command responses
 */
public function subscribeToCommandResponses(callable $callback): bool
{
    return $this->subscribe('iot/commands/+/response', $callback);
}
```

### 5. Enhanced MQTT Listener Command

#### Update for Device Management
```php
// app/Console/Commands/MqttListenerCommand.php - Add methods

/**
 * Handle device status updates
 */
private function handleDeviceStatus($topic, $message)
{
    try {
        $data = json_decode($message, true);
        $stationId = $data['station_id'] ?? null;
        
        if (!$stationId) return;
        
        $device = Device::where('station_id', $stationId)->first();
        if (!$device) return;
        
        $device->update([
            'mqtt_last_seen' => now(),
            'mqtt_connected' => $data['connected'] ?? true,
            'status' => $data['status'] ?? 'online'
        ]);
        
        $this->info("Device status updated: {$stationId} - {$data['status']}");
        
    } catch (\Exception $e) {
        $this->error('Error processing device status: ' . $e->getMessage());
    }
}

/**
 * Handle device heartbeat
 */
private function handleDeviceHeartbeat($topic, $message)
{
    try {
        $data = json_decode($message, true);
        $stationId = $data['station_id'] ?? null;
        
        if (!$stationId) return;
        
        Device::where('station_id', $stationId)
            ->update([
                'mqtt_last_seen' => now(),
                'mqtt_connected' => true,
                'status' => 'online'
            ]);
            
    } catch (\Exception $e) {
        $this->error('Error processing heartbeat: ' . $e->getMessage());
    }
}
```

### 6. New Routes for Device Commands

#### Add to routes/web.php
```php
// Device command routes (Admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('devices/{device}')->group(function () {
        Route::post('/command/reboot', [DeviceCommandController::class, 'rebootDevice'])
            ->name('devices.command.reboot');
        Route::post('/command/update-config', [DeviceCommandController::class, 'updateConfig'])
            ->name('devices.command.update-config');
        Route::post('/command/update-firmware', [DeviceCommandController::class, 'updateFirmware'])
            ->name('devices.command.update-firmware');
    });
});
```

### 7. Frontend Enhancements

#### Real-time Status Indicators
```javascript
// Add to dashboard JavaScript
function updateDeviceStatus() {
    fetch('/dashboard/mqtt-status')
        .then(response => response.json())
        .then(data => {
            data.devices.forEach(device => {
                const card = document.querySelector(`[data-device-id="${device.id}"]`);
                if (card) {
                    const statusIndicator = card.querySelector('.mqtt-status');
                    statusIndicator.className = `mqtt-status ${device.mqtt_connected ? 'connected' : 'disconnected'}`;
                    statusIndicator.textContent = device.mqtt_connected ? 'MQTT Connected' : 'MQTT Disconnected';
                }
            });
        });
}

// Update every 30 seconds
setInterval(updateDeviceStatus, 30000);
```

## Implementation Priority

### Phase 1: Core MQTT Integration (High Priority)
1. ✅ Basic MQTT service and controllers (Already implemented)
2. 🔄 Replace dashboard simulation with real MQTT commands
3. 🔄 Add device MQTT status tracking

### Phase 2: Enhanced Device Management (Medium Priority)
1. Device command controller implementation
2. Real-time status updates in dashboard
3. MQTT heartbeat system

### Phase 3: Advanced Features (Low Priority)
1. Firmware update capabilities
2. Device configuration management via MQTT
3. Command history and logging
4. Device performance monitoring

## Benefits of MQTT Device Management

1. **Real-time Communication**: Instant commands and status updates
2. **Bidirectional Control**: Send commands and receive confirmations
3. **Better Monitoring**: Live connection status and heartbeat
4. **Scalability**: Handle hundreds of devices efficiently
5. **Reliability**: QoS levels and automatic reconnection
6. **Advanced Features**: OTA updates, remote configuration

## Migration Notes

- Legacy REST endpoints remain functional during transition
- Gradual migration allows testing MQTT alongside existing HTTP
- Database schema changes are additive (no breaking changes)
- Frontend updates can be implemented incrementally
- ESP32 firmware needs updates to support new MQTT topics

## ESP32 Firmware Requirements

### New Topics to Implement:
- `iot/devices/{station_id}/status` - Device status broadcasts
- `iot/devices/{station_id}/heartbeat` - Regular heartbeat messages
- `iot/commands/{station_id}/request_data` - Data request commands
- `iot/commands/{station_id}/reboot` - Reboot commands
- `iot/commands/{station_id}/firmware_update` - OTA update commands
- `iot/commands/{station_id}/response` - Command acknowledgments

### Sample ESP32 Implementation:
```cpp
// Publish heartbeat every 60 seconds
void publishHeartbeat() {
    StaticJsonDocument<200> doc;
    doc["station_id"] = STATION_ID;
    doc["timestamp"] = getTimestamp();
    doc["uptime"] = millis();
    doc["free_memory"] = ESP.getFreeHeap();
    
    String payload;
    serializeJson(doc, payload);
    client.publish("iot/devices/" + String(STATION_ID) + "/heartbeat", payload.c_str());
}

// Handle incoming commands
void onMqttMessage(char* topic, byte* payload, unsigned int length) {
    String topicStr = String(topic);
    
    if (topicStr.startsWith("iot/commands/")) {
        handleCommand(topicStr, payload, length);
    }
}

void handleCommand(String topic, byte* payload, unsigned int length) {
    StaticJsonDocument<300> doc;
    deserializeJson(doc, payload, length);
    
    String command = doc["command"];
    
    if (command == "request_data") {
        // Force immediate sensor reading
        readSensorsAndPublish(true);
    } else if (command == "reboot") {
        // Send acknowledgment then reboot
        publishCommandResponse("reboot", "success", "Rebooting device");
        delay(1000);
        ESP.restart();
    }
}
```