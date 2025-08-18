# MQTT Deep Sleep Handling for ESP32 Devices

## Updated Implementation Plan

### (a) Connection Status & Real-time Heartbeat
- Add MQTT connection tracking fields to devices table (`mqtt_last_seen`, `mqtt_connected`, `mqtt_heartbeat_interval`)
- Update Device model with `isMqttOnline()` method to check heartbeat status
- **Implement heartbeat system where ESP32 sends status messages every 2 minutes**
- Update MQTT listener to process heartbeat messages and update device status
- Set heartbeat timeout threshold to 4-5 minutes (2x heartbeat interval)

### (b) Request Update Queue for Deep Sleep Mode
- Create queue mechanism that stores pending commands when device is offline
- Modify dashboard to queue data requests instead of failing when device is in deep sleep
- Implement command delivery system that sends queued requests when device comes online
- Add command acknowledgment system for reliable delivery

### (c) Online Update Delivery
- Replace current HTTP simulation with real MQTT command publishing
- Update DashboardController to send actual MQTT commands to `iot/commands/{station_id}/request_data`
- Implement device command responses via `iot/commands/{station_id}/response` topic
- Add real-time status updates when device comes online

### (d) Data Collection Time Updates
- **Configure ESP32 to automatically send DHT11 sensor data every 30 minutes**
- Enhance MQTT listener to handle both scheduled data collection (30min) and on-demand requests
- Update device configuration via MQTT for dynamic interval changes (modify 30min default)
- Implement automatic data request triggering based on collection schedules
- Add device configuration management through MQTT commands for both heartbeat (2min) and data intervals (30min)

The system will provide real-time bidirectional communication with 2-minute status updates, 30-minute automatic data collection, reliable command delivery for sleeping devices, and comprehensive device status monitoring.

## Problem: ESP32 Deep Sleep vs Real-time MQTT

When ESP32 devices are in deep sleep mode to conserve battery, they cannot receive MQTT messages immediately. This creates a challenge for real-time device management.

## Current Behavior

### What Happens Now:
1. User clicks "Request Data" in dashboard
2. Laravel publishes MQTT command to `iot/commands/{station_id}/request_data`
3. ESP32 is in deep sleep (WiFi off)
4. MQTT message is lost
5. Dashboard shows: "Device appears to be offline"

## Solution Strategies

### Strategy 1: Retained Messages + Wake-up Check ✅ **RECOMMENDED**

#### Implementation:
```php
// Update MqttService to use retained messages for commands
public function sendDeviceCommand(string $stationId, string $command, array $data = []): bool
{
    $topic = "iot/commands/{$stationId}/{$command}";
    $message = json_encode(array_merge($data, [
        'command' => $command,
        'station_id' => $stationId,
        'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
        'expires_at' => now()->addMinutes(30)->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
    ]));
    
    // Publish with retain=true so message persists until ESP32 connects
    return $this->publish($topic, $message, 0, true); // QoS=0, retain=true
}
```

#### ESP32 Implementation:
```cpp
void setup() {
    // After waking up and connecting to WiFi/MQTT
    connectToMQTT();
    
    // Subscribe to retained command topics
    String commandTopic = "iot/commands/" + String(STATION_ID) + "/+";
    client.subscribe(commandTopic.c_str());
    
    // Check for any pending commands (retained messages)
    delay(2000); // Wait for retained messages
    
    // Process any received commands before starting normal operation
    client.loop();
}

void onMqttMessage(char* topic, byte* payload, unsigned int length) {
    StaticJsonDocument<300> doc;
    deserializeJson(doc, payload, length);
    
    String command = doc["command"];
    String expiresAt = doc["expires_at"];
    
    // Check if command has expired
    if (isCommandExpired(expiresAt)) {
        // Clear the retained message
        client.publish(topic, "", true);
        return;
    }
    
    // Process the command
    if (command == "request_data") {
        readSensorsAndPublish(true);
        
        // Send acknowledgment
        publishCommandResponse("request_data", "completed", "Data sent from deep sleep wake-up");
        
        // Clear the retained command message
        client.publish(topic, "", true);
    }
}
```

### Simplified ESP32 Implementation

#### ESP32 Loop with Heartbeat-Based Configuration:
```cpp
// Configuration variables (received from server via heartbeat response)
int intervalTime = 300;            // Default 5 minutes (will be updated by server)
int dataCollectionTime = 1800;     // Default 30 minutes (will be updated by server)
unsigned long referenceTime = 0;   // Last data collection time
bool updateRequest = false;         // Server request flag

void loop() {
    // Step 1: ESP32 wakes up from deep sleep
    
    // Step 2: Establish WiFi
    if (!connectToWiFi()) {
        ESP.deepSleep(60 * 1000000); // Sleep 1 minute if WiFi fails
        return;
    }
    
    // Step 3: Connect to MQTT
    if (!connectToMQTT()) {
        ESP.deepSleep(60 * 1000000); // Sleep 1 minute if MQTT fails
        return;
    }
    
    // Step 4: Send heartbeat request
    sendHeartbeatRequest();
    
    // Step 5: Wait for heartbeat response (configuration)
    waitForHeartbeatResponse();
    
    // Step 6: Check if data reading is needed
    bool readData = false;
    if (updateRequest) {
        readData = true;
    } else {
        unsigned long currentTime = millis();
        if ((currentTime - referenceTime) >= (dataCollectionTime * 1000)) {
            readData = true;
            referenceTime = currentTime;
        }
    }
    
    // Step 7: Read and send data if needed
    if (readData) {
        readSensorsAndSendData();
    }
    
    // Step 8: Go to deep sleep based on intervalTime
    ESP.deepSleep(intervalTime * 1000000); // Convert seconds to microseconds
}

void sendHeartbeatRequest() {
    StaticJsonDocument<200> doc;
    doc["station_id"] = STATION_ID;
    doc["timestamp"] = getCurrentTimestamp();
    doc["battery_voltage"] = readBatteryVoltage();
    doc["wifi_rssi"] = WiFi.RSSI();
    doc["uptime_seconds"] = millis() / 1000;
    
    String payload;
    serializeJson(doc, payload);
    
    client.publish("iot/heartBeat/request", payload.c_str());
}

void waitForHeartbeatResponse() {
    client.subscribe("iot/heartBeat/response");
    
    unsigned long startTime = millis();
    while (millis() - startTime < 5000) { // Wait 5 seconds for response
        client.loop();
        delay(100);
    }
}

void onMqttMessage(char* topic, byte* payload, unsigned int length) {
    String topicStr = String(topic);
    
    if (topicStr == "iot/heartBeat/response") {
        handleHeartbeatResponse(payload, length);
    } else if (topicStr == "iot/data/response") {
        handleDataResponse(payload, length);
    }
}

void handleHeartbeatResponse(byte* payload, unsigned int length) {
    StaticJsonDocument<300> doc;
    deserializeJson(doc, payload, length);
    
    // Update configuration from server
    intervalTime = doc["intervalTime"] | 300;
    dataCollectionTime = doc["dataCollectionTime"] | 1800;
    updateRequest = doc["updateRequest"] | false;
    
    // Sync time with server
    String serverTime = doc["serverTime"];
    // Update device RTC if needed
}

void readSensorsAndSendData() {
    StaticJsonDocument<300> doc;
    doc["station_id"] = STATION_ID;
    doc["timestamp"] = getCurrentTimestamp();
    doc["humidity"] = dht.readHumidity();
    doc["temperature"] = dht.readTemperature();
    doc["rssi"] = WiFi.RSSI();
    doc["battery_voltage"] = readBatteryVoltage();
    doc["update_request"] = updateRequest;
    
    String payload;
    serializeJson(doc, payload);
    
    client.publish("iot/data/upload", payload.c_str());
    
    // Wait for data response
    client.subscribe("iot/data/response");
    unsigned long startTime = millis();
    while (millis() - startTime < 3000) {
        client.loop();
        delay(100);
    }
}
```
```

### Strategy 3: Hybrid Approach - Request Queue System

#### Laravel Implementation:
```php
// Create command queue table
Schema::create('device_commands', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('device_id');
    $table->string('command');
    $table->json('payload');
    $table->enum('status', ['pending', 'sent', 'completed', 'expired']);
    $table->timestamp('expires_at');
    $table->timestamps();
    
    $table->foreign('device_id')->references('id')->on('devices');
    $table->index(['device_id', 'status']);
});

// Updated Dashboard Controller
public function requestUpdate($id)
{
    try {
        $device = Device::findOrFail($id);
        
        // Create command record
        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command' => 'request_data',
            'payload' => [
                'priority' => 'high',
                'requested_by' => auth()->user()->name,
                'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s')
            ],
            'status' => 'pending',
            'expires_at' => now()->addHours(2) // Command expires in 2 hours
        ]);
        
        // Try immediate MQTT delivery
        $mqttSuccess = $this->mqttService->sendDeviceCommand(
            $device->station_id, 
            'request_data', 
            $command->payload
        );
        
        if ($mqttSuccess) {
            $command->update(['status' => 'sent']);
            return response()->json([
                'success' => true,
                'message' => 'Command sent via MQTT (or queued if device sleeping)',
                'command_id' => $command->id,
                'method' => 'mqtt_with_queue'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Command queued for next device connection',
                'command_id' => $command->id,
                'method' => 'queue_only'
            ]);
        }
        
    } catch (\Exception $e) {
        \Log::error('Command queue error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to queue command'], 500);
    }
}
```

## Recommended Implementation

### Step 1: Update MqttService for Retained Messages
```php
// app/Services/MqttService.php
public function publish(string $topic, string $message, int $qos = 0, bool $retain = false): bool
{
    try {
        if (!$this->isConnected && !$this->connect()) {
            return false;
        }

        $this->getConnection()->publish($topic, $message, $qos, $retain);
        
        Log::info('MQTT message published', [
            'topic' => $topic,
            'message_length' => strlen($message),
            'qos' => $qos,
            'retain' => $retain
        ]);

        return true;
    } catch (\Exception $e) {
        Log::error('MQTT publish failed: ' . $e->getMessage());
        return false;
    }
}

public function sendDeviceCommand(string $stationId, string $command, array $data = []): bool
{
    $topic = "iot/commands/{$stationId}/{$command}";
    $message = json_encode(array_merge($data, [
        'command' => $command,
        'station_id' => $stationId,
        'timestamp' => now()->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
        'expires_at' => now()->addMinutes(30)->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
        'id' => uniqid()
    ]));
    
    // Use retained messages so ESP32 gets command on next wake-up
    return $this->publish($topic, $message, 0, true);
}
```

### Step 2: Update Dashboard Controller
```php
// app/Http/Controllers/DashboardController.php
public function requestUpdate($id)
{
    try {
        $device = Device::findOrFail($id);
        
        // Send MQTT command with retention
        $command = [
            'command' => 'request_data',
            'station_id' => $device->station_id,
            'priority' => 'high',
            'requested_by' => auth()->user()->name
        ];

        $mqttSuccess = $this->mqttService->sendDeviceCommand(
            $device->station_id, 
            'request_data', 
            $command
        );

        if ($mqttSuccess) {
            $device->update(['request_update' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Command sent to device (will be delivered on next wake-up if sleeping)',
                'method' => 'mqtt_retained',
                'note' => 'ESP32 will receive command when it next connects to MQTT'
            ]);
        } else {
            $device->update(['request_update' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Command queued via request_update flag',
                'method' => 'config_poll_fallback'
            ]);
        }
        
    } catch (\Exception $e) {
        \Log::error('Deep sleep command error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to send command'], 500);
    }
}
```

### Step 3: ESP32 Firmware Update
```cpp
// ESP32 main loop for deep sleep devices
static int heartbeatCycle = 0;

void loop() {
    heartbeatCycle++;
    
    // 1. Wake up and connect
    connectToWiFi();
    connectToMQTT();
    
    // 2. Check for retained commands first
    checkForPendingCommands();
    
    // 3. Send heartbeat every cycle (every 2 minutes)
    publishHeartbeat();
    
    // 4. Send DHT11 data every 15 cycles (every 30 minutes)
    if (heartbeatCycle >= DATA_COLLECTION_CYCLES) {
        readSensorsAndPublish(false);
        heartbeatCycle = 0; // Reset counter
    }
    
    // 5. Poll for configuration (includes request_update flag)
    pollConfiguration();
    
    // 6. Go back to deep sleep for 2 minutes
    WiFi.disconnect();
    ESP.deepSleep(HEARTBEAT_DURATION);
}

void checkForPendingCommands() {
    String commandTopic = "iot/commands/" + String(STATION_ID) + "/+";
    client.subscribe(commandTopic.c_str());
    
    // Wait for retained messages
    unsigned long startTime = millis();
    while (millis() - startTime < 3000) {
        client.loop();
        if (commandProcessed) break;
        delay(100);
    }
}
```

## Dashboard User Experience

### Message Examples:

#### For Deep Sleep Devices:
- ✅ **"Command sent to device (will be delivered on next wake-up if sleeping)"**
- 📱 **"ESP32 will receive command when it next connects to MQTT"**
- ⏰ **"Expected delivery: within next 15 minutes (based on sleep schedule)"**

#### For Always-on Devices:
- ✅ **"Command sent via MQTT at 2025-08-17 15:30:45"**
- 📡 **"Device should respond immediately"**

## Benefits of This Approach

1. **Battery Efficiency**: ESP32 can stay in deep sleep for extended periods
2. **Reliable Delivery**: Retained messages ensure commands aren't lost
3. **User Awareness**: Clear messaging about delivery timing
4. **Fallback Support**: Multiple delivery mechanisms
5. **Command Expiration**: Prevents old commands from executing
6. **Acknowledgment**: ESP32 can confirm command execution

## Configuration Options

```env
# .env additions for deep sleep handling
MQTT_COMMAND_RETENTION=true
MQTT_COMMAND_EXPIRY_MINUTES=30
DEVICE_SLEEP_TOLERANCE_MINUTES=20
```

This approach ensures reliable command delivery to ESP32 devices regardless of their sleep state! 🔋⚡