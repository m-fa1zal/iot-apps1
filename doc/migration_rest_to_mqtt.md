# Migration from REST API to MQTT Broker

## Overview
This document outlines the migration from HTTP REST API communication to MQTT broker communication for ESP32 device integration.

## Current REST API Endpoints (DEPRECATED)

### Location: `app/Http/Controllers/Api/Legacy/ESP32Controller.php`

#### 1. Configuration Endpoint
- **URL**: `POST /api/config`
- **Purpose**: ESP32 devices retrieve configuration and check for update requests
- **Status**: ⚠️ DEPRECATED - Moved to Legacy folder

#### 2. Data Upload Endpoint  
- **URL**: `POST /api/upload`
- **Purpose**: ESP32 devices upload sensor data
- **Status**: ⚠️ DEPRECATED - Moved to Legacy folder

## Migration Plan

### Phase 1: Relocate REST Endpoints ✅ COMPLETED
- [x] Move ESP32Controller to `app/Http/Controllers/Api/Legacy/`
- [x] Update namespace from `App\Http\Controllers\Api` to `App\Http\Controllers\Api\Legacy`
- [x] Update route imports in `routes/api.php`
- [x] Add deprecation notices to route comments
- [x] Add `@deprecated` annotations to controller methods

### Phase 2: MQTT Implementation ✅ COMPLETED
- [x] Install Laravel MQTT package (php-mqtt/laravel-client v1.6.1 by Marvin Mall)
- [x] Publish MQTT configuration from Laravel client package
- [x] Update environment variables for Laravel client integration
- [x] Implement MQTT publishers for device commands using Laravel facade
- [x] Implement MQTT subscribers for sensor data
- [x] Create MQTT-based device management controllers
- [x] Add MQTT API routes (/api/mqtt/*)
- [x] Create MQTT listener command (php artisan mqtt:listen)
- [x] Create comprehensive testing documentation

### Phase 3: ESP32 Code Migration (TODO)
- [ ] Update ESP32 firmware to use MQTT client
- [ ] Replace HTTP POST requests with MQTT publish/subscribe
- [ ] Implement MQTT topics structure
- [ ] Test bidirectional MQTT communication

### Phase 4: Legacy Cleanup (TODO)
- [ ] Remove REST API endpoints after successful MQTT migration
- [ ] Update documentation
- [ ] Remove unused dependencies

## ESP32-Server MQTT Communication Process

### MQTT Topics:
- `iot/heartBeat/request` → ESP32 → Server: Heartbeat with status, send update device is online
- `iot/heartBeat/response` → Server → ESP32: Get Update Request and Device Configuration Request  
- `iot/config/request` → ESP32 → Server: Update Device Configuration is Completed, set configurationUpdate to FALSE in database
- `iot/config/response` → Server → ESP32: Server acknowledge Device Configuration is Completed
- `iot/data/request` → ESP32 → Server: Upload latest sensor data. set updateRequest to FALSE in database
- `iot/data/response` → Server → ESP32: Server acknowledge Sensor Data Uploaded is Completed

### New Process for ESP32 and Server:

**Step 1:** ESP32 in deep sleep

**Step 2:** ESP32 Wake Up. Boot the device

**Step 3:** rtcData save device configuration
```
IF (First Boot OR rtcData is corrupted) THEN
    set configuration_update = true;
END IF
```

**Step 4:** ESP32 Establish WiFi
```
IF WiFi not established THEN
    ESP32 will sleep for 1 minute (60 seconds)
END IF
```

**Step 5:** ESP32 send heartbeat request to MQTT Broker → `iot/heartBeat/request`
```json
{
    "station_id": "ST-NH9-1001",
    "task": "heartbeat",
    "message": "data send",
    "status": "online"
}
```

**Step 6:** Server send response → `iot/heartBeat/response`
```json
{
    "station_id": "ST-NH9-1001",
    "task": "heartbeat",
    "message": "data received",
    "success": true,
    "request_update": false,
    "configuration_update": false
}
```
- Set `request_update = request_update`
- Set `configuration_update = configuration_update`

**Step 7:** Check if there is configuration changes or not
```
IF configuration_update is TRUE THEN
    ESP32 send configuration update complete (configuration_update = FALSE) to MQTT Broker → iot/config/request
```
```json
{
    "station_id": "ST-NH9-1001",
    "task": "Configuration Update",
    "message": "data send",
    "configuration_update": false
}
```
```
    Server send response → iot/config/response
```
```json
{
    "station_id": "ST-NH9-1001",
    "task": "Configuration Update", 
    "message": "data received",
    "success": true,
    "data_collection_time": 30,
    "data_interval": 3
}
```
```
    set dataCollectionTime = data_collection_time
    set intervalTime = data_interval
END IF
```

**Step 8:** Check if user having real-time update request
```
IF request_update is TRUE THEN
    set readData = TRUE
ELSE
    set periodTime = currentTime - referenceTime
    IF periodTime >= dataCollectionTime THEN
        set readData = TRUE
        set referenceTime = currentTime
    END IF
END IF
```

**Step 9:** Check either need to read data from DHT11
```
IF readData is TRUE THEN
    read humidity, temperature from DHT11    
    read battery voltage, battery_voltage
    read WIFI RSSI, rssi
    set request_update = FALSE
    
    ESP32 send data(humidity,temperature,battery voltage, WIFI RSSI) to MQTT Broker → iot/data/request
```
```json
{
    "station_id": "ST-NH9-1001",
    "task": "Upload Data",
    "message": "data send",
    "humidity": 65.5,
    "temperature": 28.3,
    "rssi": -67,
    "battery_voltage": 3.85,
    "update_request": false
}
```
```
    Server send response to ESP32 → iot/data/response
```
```json
{
    "station_id": "ST-NH9-1001",
    "task": "Upload Data",
    "message": "data received",
    "success": true
}
```
```
END IF
```

**Step 10:** ESP32 will sleep based on data_interval

## MQTT Topics Structure

```
iot/
├── heartBeat/
│   ├── request                 # ESP32 → Server: Heartbeat with status, update device online
│   └── response                # Server → ESP32: Update request and device configuration request
├── config/
│   ├── request                 # ESP32 → Server: Configuration update completed
│   └── response                # Server → ESP32: Acknowledge configuration completed
└── data/
    ├── request                 # ESP32 → Server: Upload sensor data
    └── response                # Server → ESP32: Acknowledge data upload completed
```

### Topics Definition:
- `iot/heartBeat/request` - ESP32 sends heartbeat and device status
- `iot/heartBeat/response` - Server sends update requests and configuration status
- `iot/config/request` - ESP32 confirms configuration update completion
- `iot/config/response` - Server acknowledges configuration update and sends new config values
- `iot/data/request` - ESP32 uploads sensor data
- `iot/data/response` - Server acknowledges sensor data reception
```

## Process Flow Summary

This comprehensive approach provides:

1. **Simple Topics**: Only 6 MQTT topics needed (3 request/response pairs)
2. **Clear Flow**: 10-step process with detailed logic flow
3. **Minimal Payloads**: Only essential data in JSON messages
4. **Battery Efficient**: Deep sleep between cycles based on data_interval
5. **Flexible Timing**: Both scheduled and on-demand data collection
6. **Error Handling**: WiFi connection retry with fallback sleep
7. **Configuration Management**: rtcData persistence with corruption detection
8. **Real-time Updates**: Server-controlled update requests via flags

### Key Features:
- **Heartbeat**: ESP32 requests configuration status on every wake-up
- **Dynamic Intervals**: Server controls sleep and data collection timing via config
- **Update Requests**: Real-time data requests via `request_update` flag
- **Configuration Updates**: Server sends updated config values in response
- **Status Tracking**: Battery, WiFi RSSI, and device status monitoring
- **Persistent Storage**: rtcData maintains configuration between deep sleep cycles

## Benefits of MQTT Migration

1. **Real-time Communication**: Instant bidirectional messaging
2. **Reduced Bandwidth**: Lightweight protocol compared to HTTP
3. **Better Scalability**: Handle thousands of devices efficiently  
4. **Built-in QoS**: Quality of Service levels for reliable delivery
5. **Connection Management**: Automatic reconnection and session management
6. **Event-driven Architecture**: React to device events in real-time

## Current Status

- ✅ **Phase 1 Complete**: REST endpoints relocated to Legacy folder
- ✅ **Phase 2 Complete**: MQTT implementation ready for production
- 🔄 **Phase 3 Ready**: ESP32 firmware updates required
- ⏳ **Phase 4 Pending**: Legacy cleanup after migration

## MQTT Implementation Details

### New Files Created:
- `config/mqtt-client.php` - MQTT broker configuration (published from laravel-client)
- `app/Services/MqttService.php` - MQTT client service (using Laravel facade)
- `app/Http/Controllers/Api/MqttConfigController.php` - MQTT config endpoints
- `app/Http/Controllers/Api/MqttDataController.php` - MQTT data endpoints  
- `app/Console/Commands/MqttListenerCommand.php` - Background MQTT listener
- `doc/mqtt_test_guide.md` - Comprehensive testing guide

### Package Information:
- **Package**: php-mqtt/laravel-client v1.6.1
- **Maintainer**: Marvin Mall
- **Features**: Laravel facade, auto-discovery, multiple connections, configuration management

### New Routes:
- `POST /api/mqtt/config/request` - Send config request via MQTT
- `POST /api/mqtt/config/get` - Get device configuration
- `POST /api/mqtt/data/receive` - Process sensor data via MQTT
- `POST /api/mqtt/data/request` - Request data from device via MQTT

### MQTT Commands:
- `php artisan mqtt:listen` - Start MQTT message listener
- `php artisan mqtt:listen --timeout=3600` - Listen with custom timeout

### Environment Configuration:
Updated MQTT settings in `.env` file for Laravel client configuration:
```
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_iot_esp32
MQTT_CLEAN_SESSION=true
MQTT_ENABLE_LOGGING=true
MQTT_AUTH_USERNAME=
MQTT_AUTH_PASSWORD=
MQTT_CONNECT_TIMEOUT=60
MQTT_SOCKET_TIMEOUT=5
MQTT_RESEND_TIMEOUT=10
MQTT_KEEP_ALIVE_INTERVAL=60
```

## Notes

- Legacy REST endpoints remain functional during migration
- Gradual migration allows testing MQTT alongside existing REST API
- Device API tokens will be repurposed for MQTT authentication
- Database schema remains unchanged (devices, sensor_readings tables)