# MQTT Implementation Testing Guide

## Overview
This guide provides instructions for testing the MQTT implementation in the Laravel IoT application.

## MQTT Endpoints

### 1. Configuration Endpoints

#### Request Configuration via MQTT
- **URL**: `POST /api/mqtt/config/request`
- **Purpose**: Send configuration request to ESP32 device via MQTT
- **Authentication**: API Token (Bearer or parameter)

#### Get Configuration (Direct)
- **URL**: `POST /api/mqtt/config/get`
- **Purpose**: Get device configuration directly (without MQTT)
- **Authentication**: API Token (Bearer or parameter)

### 2. Data Endpoints

#### Receive Data via MQTT
- **URL**: `POST /api/mqtt/data/receive`
- **Purpose**: Process sensor data upload from ESP32 via MQTT
- **Authentication**: API Token (Bearer or parameter)

#### Request Data via MQTT
- **URL**: `POST /api/mqtt/data/request`
- **Purpose**: Send data request to ESP32 device via MQTT
- **Authentication**: API Token (Bearer or parameter)

## MQTT Topics

### ESP32 Implementation Topics:
- **iot/config/request** - Server → ESP32: Configuration requests
- **iot/config/response** - ESP32 → Server: Configuration acknowledgments
- **iot/data/upload** - ESP32 → Server: Sensor data uploads
- **iot/data/response** - Server → ESP32: Data upload acknowledgments

## MQTT Listener Command

### Start MQTT Listener
```bash
php artisan mqtt:listen
```

### Start with Custom Timeout
```bash
php artisan mqtt:listen --timeout=7200  # 2 hours
```

The MQTT listener will:
- Connect to MQTT broker
- Subscribe to data upload topic (`iot/data/upload`)
- Subscribe to config response topic (`iot/config/response`)
- Process incoming messages from ESP32 devices
- Save sensor data to database
- Send responses back to ESP32

## Configuration

### Environment Variables (.env)
```
# MQTT Configuration
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=laravel_iot_esp32
MQTT_CLEAN_SESSION=true
MQTT_KEEP_ALIVE=60
MQTT_QOS=0
MQTT_CONNECTION_TIMEOUT=60
MQTT_SOCKET_TIMEOUT=5
MQTT_RESEND_TIMEOUT=10
```

### MQTT Configuration File
Location: `config/mqtt.php`

## Testing Workflow

### Phase 1: Setup MQTT Broker
1. Install and configure MQTT broker (Mosquitto, RabbitMQ, etc.)
2. Update MQTT configuration in `.env` file
3. Test MQTT broker connectivity

### Phase 2: Test MQTT Service
1. Start MQTT listener:
   ```bash
   php artisan mqtt:listen
   ```

2. Test config request endpoint:
   ```bash
   curl -X POST http://localhost:8080/api/mqtt/config/request \
        -H "Authorization: Bearer YOUR_API_TOKEN" \
        -H "Content-Type: application/json"
   ```

### Phase 3: Test ESP32 Integration
1. Update ESP32 firmware to use MQTT
2. Configure ESP32 with MQTT broker details
3. Test bidirectional communication:
   - ESP32 sends data to `iot/data/upload`
   - Laravel processes and responds via `iot/data/response`
   - ESP32 requests config via `iot/config/request`
   - Laravel responds with configuration

### Phase 4: Test Data Flow
1. ESP32 publishes sensor data to `iot/data/upload`
2. MQTT listener processes the data
3. Data is saved to database
4. Response is sent back to ESP32 via `iot/data/response`

## JSON Message Formats

### Config Request/Response
```json
{
  "serverTime": "2025-08-17 11:08:30",
  "updateRequest": false,
  "nextCheckInterval": 300,
  "station_id": "ST-NH9-1001",
  "data_collection_time": 1800
}
```

### Data Upload
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

### Data Response
```json
{
  "success": true,
  "message": "Sensor data received successfully",
  "reading_id": 45,
  "timestamp": "2025-08-17 11:46:10"
}
```

## Testing with MQTT Client

### Using Mosquitto Client Tools

#### Subscribe to topics:
```bash
mosquitto_sub -h localhost -p 1883 -t "iot/data/upload"
mosquitto_sub -h localhost -p 1883 -t "iot/config/response"
```

#### Publish test messages:
```bash
# Test data upload
mosquitto_pub -h localhost -p 1883 -t "iot/data/upload" -m '{
  "station_id": "ST-NH9-1001",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": false
}'

# Test config response
mosquitto_pub -h localhost -p 1883 -t "iot/config/response" -m '{
  "station_id": "ST-NH9-1001",
  "status": "config_received",
  "timestamp": "2025-08-17 11:08:30"
}'
```

## Troubleshooting

### Common Issues:

1. **MQTT Connection Failed**
   - Check MQTT broker is running
   - Verify host and port in `.env`
   - Check firewall settings

2. **Messages Not Received**
   - Verify topic names match ESP32 implementation
   - Check QoS settings
   - Ensure MQTT listener is running

3. **Database Errors**
   - Check device exists and is active
   - Verify station_id matches database
   - Check API token authentication

4. **JSON Parse Errors**
   - Validate JSON format
   - Check required fields are present
   - Verify data types match validation rules

## Migration Benefits

### Compared to REST API:
- **Real-time Communication**: Instant bidirectional messaging
- **Lower Bandwidth**: Lightweight protocol
- **Better Scalability**: Handle thousands of devices
- **Built-in QoS**: Quality of Service levels
- **Connection Management**: Automatic reconnection
- **Event-driven**: React to device events in real-time

## Next Steps

1. **Production Deployment**: Deploy MQTT broker and Laravel app
2. **ESP32 Firmware Update**: Migrate ESP32 code to MQTT
3. **Performance Testing**: Test with multiple devices
4. **Monitoring Setup**: Implement MQTT monitoring and logging
5. **Legacy Cleanup**: Remove REST API endpoints after migration