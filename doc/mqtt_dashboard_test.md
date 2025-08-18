# MQTT Dashboard Test Guide

## Overview
This guide provides steps to test the new MQTT-based "Request Data" functionality in the dashboard.

## ✅ What Changed

### Before (HTTP Simulation):
- "Request Data" button created fake sensor readings with random variations
- Generated simulated data immediately in the browser
- No actual communication with ESP32 devices

### After (Simplified MQTT):
- "Request Data" button sets updateRequest flag for device
- Device gets flag on next heartbeat response
- Simple heartbeat-based communication
- No complex command topics needed

## Testing Steps

### 1. Prerequisites
- MQTT broker running (e.g., Mosquitto)
- Laravel application running
- At least one device in the database
- MQTT configuration in `.env` file

### 2. Start MQTT Listener
```bash
cd /path/to/iot-apps1
php artisan mqtt:listen
```

### 3. Test Dashboard Request Data

#### 3.1 Access Dashboard
- Visit: `http://localhost:8080/dashboard`
- Login with admin credentials

#### 3.2 Test Device Request
1. Click "Request Data" button on any device card
2. **Expected Result**: 
   - Message: "Data request queued - device will receive on next heartbeat"
   - Method: "heartbeat_flag"
   - Note: "ESP32 will get updateRequest=true in next heartbeat response"

#### 3.3 Test Offline Device
1. Ensure device hasn't been active for >10 minutes
2. Click "Request Data" button
3. **Expected Result**:
   - Error message: "Device appears to be offline (no recent activity)"
   - Shows last seen timestamp

#### 3.4 Test Heartbeat Flow
1. Start MQTT listener: `php artisan mqtt:listen`
2. Simulate ESP32 heartbeat request to `iot/heartBeat/request`
3. **Expected Result**:
   - Server responds with updateRequest=true if flag was set
   - Device gets configuration with request flag

## Simplified MQTT Flow

### 1. Heartbeat Request (ESP32 → Server)
**Topic**: `iot/heartBeat/request`
```json
{
  "station_id": "ST-NH9-1001",
  "timestamp": "2025-08-17 11:08:30",
  "battery_voltage": 3.85,
  "wifi_rssi": -67,
  "uptime_seconds": 120
}
```

### 2. Heartbeat Response (Server → ESP32)
**Topic**: `iot/heartBeat/response`
```json
{
  "station_id": "ST-NH9-1001",
  "serverTime": "2025-08-17 11:08:30",
  "updateRequest": true,
  "intervalTime": 300,
  "dataCollectionTime": 1800
}
```

### 3. Data Upload (ESP32 → Server)
**Topic**: `iot/data/upload`
```json
{
  "station_id": "ST-NH9-1001",
  "timestamp": "2025-08-17 11:08:30",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": true
}
```

### 4. Data Response (Server → ESP32)
**Topic**: `iot/data/response`
```json
{
  "station_id": "ST-NH9-1001",
  "success": true,
  "message": "Data received",
  "timestamp": "2025-08-17 11:08:35"
}
```

## Testing Results Checklist

### ✅ Backend Changes:
- [x] DashboardController sets updateRequest flag
- [x] MQTT listener handles heartbeat requests
- [x] Device online status validation
- [x] Simplified configuration response
- [x] Proper error logging

### ✅ Frontend Changes:
- [x] Updated JavaScript for heartbeat method
- [x] Shows heartbeat_flag method
- [x] Clear user messaging about heartbeat delivery
- [x] Enhanced error handling with last seen info

### ✅ MQTT Integration:
- [x] Heartbeat request/response topics
- [x] Data upload/response topics
- [x] JSON format matches simplified flow
- [x] No complex command topics needed

## Troubleshooting

### Issue: "Device appears to be offline"
**Solution**: 
- Check device `last_seen` timestamp in database
- Ensure device has been active within 10 minutes
- Manually update `last_seen` for testing:
  ```sql
  UPDATE devices SET last_seen = NOW() WHERE id = 1;
  ```

### Issue: "Failed to send MQTT request"
**Solution**:
- Check MQTT broker is running
- Verify MQTT configuration in `.env`
- Check Laravel logs for connection errors
- Test MQTT connection manually

### Issue: Heartbeat not received by ESP32
**Solution**:
- Verify ESP32 subscribes to: `iot/heartBeat/response`
- Check topic name matches exactly
- Ensure ESP32 MQTT client is connected
- Use MQTT client tool to verify broker receives messages

## Manual MQTT Testing

### Using Mosquitto Client:

1. **Subscribe to heartbeat topics** (to see the flow):
```bash
mosquitto_sub -h localhost -p 1883 -t "iot/heartBeat/+"
mosquitto_sub -h localhost -p 1883 -t "iot/data/+"
```

2. **Simulate ESP32 heartbeat request**:
```bash
mosquitto_pub -h localhost -p 1883 -t "iot/heartBeat/request" -m '{
  "station_id": "ST-NH9-1001",
  "timestamp": "2025-08-17 11:08:30",
  "battery_voltage": 3.85,
  "wifi_rssi": -67,
  "uptime_seconds": 120
}'
```

3. **Simulate ESP32 data upload**:
```bash
mosquitto_pub -h localhost -p 1883 -t "iot/data/upload" -m '{
  "station_id": "ST-NH9-1001",
  "timestamp": "2025-08-17 11:08:30",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": true
}'
```

## Performance Benefits

### Compared to HTTP Simulation:
1. **Real Communication**: Actual device interaction via heartbeat
2. **Status Validation**: Only sends requests to online devices
3. **Simple Protocol**: Just 4 MQTT topics needed
4. **Battery Efficient**: Uses device wake-up cycles
5. **Reliable Delivery**: Flag-based system ensures delivery
6. **Scalability**: Lightweight heartbeat-based architecture

## Next Steps

1. **ESP32 Firmware Update**: Implement simplified heartbeat flow
2. **Real Device Testing**: Test with actual ESP32 hardware
3. **Performance Monitoring**: Monitor heartbeat frequency
4. **Enhanced UI**: Add heartbeat-based connection status
5. **Configuration Management**: Fine-tune intervals and timing

The dashboard now uses simplified MQTT heartbeat communication! 🎉