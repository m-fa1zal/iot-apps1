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

## MQTT Topics Structure (ESP32 Implementation)

```
iot/
├── config/
│   ├── request                 # Server → ESP32: Configuration requests
│   └── response                # ESP32 → Server: Configuration acknowledgment
└── data/
    ├── upload                  # ESP32 → Server: Sensor data
    └── response                # Server → ESP32: Data upload acknowledgment
```

### ESP32 Topics Definition:
- `iot/config/request` - Server sends configuration requests to ESP32
- `iot/config/response` - ESP32 sends configuration acknowledgments to server
- `iot/data/upload` - ESP32 uploads sensor data to server
- `iot/data/response` - Server sends upload acknowledgments to ESP32

### JSON Format:
The JSON message format remains the same as the current REST API:

**Config Request (Server → ESP32 via iot/config/request):**
```json
{
  "serverTime": "2025-08-17 11:08:30",
  "updateRequest": false,
  "nextCheckInterval": 300,
  "station_id": "ST-NH9-1001",
  "data_collection_time": 1800
}
```

**Config Response (ESP32 → Server via iot/config/response):**
```json
{
  "station_id": "ST-NH9-1001",
  "status": "config_received",
  "timestamp": "2025-08-17 11:08:30",
  "message": "Configuration applied successfully"
}
```

**Data Upload (ESP32 → Server via iot/data/upload):**
```json
{
  "station_id": "ST-NH9-1001",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": false
}
```

**Data Response (Server → ESP32 via iot/data/response):**
```json
{
  "success": true,
  "message": "Sensor data received successfully",
  "reading_id": 145,
  "device_id": 1,
  "station_id": "ST-NH9-1001",
  "timestamp": "2025-08-17 11:53:01",
  "request_update": false
}
```

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