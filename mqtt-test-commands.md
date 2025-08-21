# MQTT Test Commands for ESP32 IoT Communication

## Prerequisites
Install Mosquitto MQTT tools:
- Windows: Download from https://mosquitto.org/download/
- Or use chocolatey: `choco install mosquitto`

## Quick Commands

### 1. Subscribe to all ESP32 topics (Railway)
```powershell
mosquitto_sub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/+/+"
```

### 2. Subscribe to all ESP32 topics (Local)
```powershell
mosquitto_sub -h 10.70.33.40 -p 1883 -t "iot/JHR03004/+/+"
```

### 3. Test Heartbeat (Railway)
```powershell
# Publish heartbeat request
mosquitto_pub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/heartbeat/request" -m '{"station_id":"JHR03004","api_key":"Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg","task":"heartbeat","message":"SEND","params":{"status":"online"}}'

# Listen for response
mosquitto_sub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/heartbeat/response"
```

### 4. Test Config Request (Railway)
```powershell
# Publish config request
mosquitto_pub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/config/request" -m '{"station_id":"JHR03004","api_key":"Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg","task":"Configuration Update","message":"SEND","params":{"configuration_update":"false"}}'

# Listen for response
mosquitto_sub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/config/response"
```

### 5. Test Data Upload (Railway)
```powershell
# Publish data request
mosquitto_pub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/data/request" -m '{"station_id":"JHR03004","api_key":"Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg","task":"Upload Data","message":"SEND","params":{"humidity":65.5,"temperature":28.3,"rssi":-67,"battery_voltage":3.7,"update_request":false}}'

# Listen for response
mosquitto_sub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "iot/JHR03004/data/response"
```

## Using the PowerShell Script

### Show help
```powershell
.\test-mqtt.ps1
```

### Subscribe to all topics
```powershell
.\test-mqtt.ps1 -Action subscribe -Broker railway
.\test-mqtt.ps1 -Action subscribe -Broker local
```

### Test individual functions
```powershell
.\test-mqtt.ps1 -Action heartbeat -Broker railway -StationId JHR03004
.\test-mqtt.ps1 -Action config -Broker railway -StationId JHR03004
.\test-mqtt.ps1 -Action data -Broker railway -StationId JHR03004
```

### Run all tests
```powershell
.\test-mqtt.ps1 -Action test-all -Broker railway -StationId JHR03004
```

## Troubleshooting Steps

1. **Test broker connectivity:**
   ```powershell
   # Railway broker
   mosquitto_pub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "test/topic" -m "hello"
   
   # Local broker
   mosquitto_pub -h 10.70.33.40 -p 1883 -t "test/topic" -m "hello"
   ```

2. **Check if Laravel MQTT listener is running:**
   ```powershell
   # In Laravel project directory
   php artisan mqtt:listen
   ```

3. **Monitor all MQTT traffic:**
   ```powershell
   # Railway broker - all topics
   mosquitto_sub -h shinkansen.proxy.rlwy.net -p 41071 -u root -P k6n6fxfyrtip7gln7abr00fgtie41tkd -t "#"
   
   # Local broker - all topics
   mosquitto_sub -h 10.70.33.40 -p 1883 -t "#"
   ```

## Expected Responses

### Heartbeat Response:
```json
{
  "station_id": "JHR03004",
  "api_key": "Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg",
  "task": "heartbeat",
  "message": "RECEIVED",
  "success": true,
  "reply": {
    "current_time": 1724245802,
    "request_update": false,
    "configuration_update": false
  }
}
```

### Config Response:
```json
{
  "station_id": "JHR03004",
  "api_key": "Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg",
  "task": "Configuration Update",
  "message": "RECEIVED",
  "reply": {
    "success": true,
    "data_collection_time": 30,
    "data_interval": 3
  }
}
```

### Data Response:
```json
{
  "station_id": "JHR03004",
  "api_key": "Tv89beybSCrrRaJDQSbczfff5dmJ6pYdKqaTBJWqsCflr05m5Hg7t9WoNxzPuryg",
  "task": "Upload Data",
  "message": "RECEIVED",
  "reply": {
    "success": true
  }
}
```