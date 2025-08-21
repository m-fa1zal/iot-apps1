# IoT MQTT Communication Service

## Overview
Production-ready MQTT listener service for IoT device communication. Handles heartbeat, configuration, and sensor data upload requests from ESP32 devices.

## Features
- ✅ **Heartbeat Management**: Device status monitoring with database flags
- ✅ **Configuration Updates**: Device timing parameters from database
- ✅ **Sensor Data Collection**: Real-time data storage to MySQL database
- ✅ **Railway Integration**: MQTT broker and database connectivity
- ✅ **Error Handling**: Robust error handling and logging
- ✅ **Production Ready**: Clean code structure with proper separation

## Files
- `mqtt-listener-production.php` - Main MQTT service
- `app/Services/MqttService.php` - Laravel MQTT service (alternative)
- `app/Console/Commands/MqttListenerCommand.php` - Artisan command

## Quick Start

### 1. Environment Setup
```bash
cp .env.example .env
# Edit .env with your Railway credentials:
# - Database settings (DB_HOST, DB_PASSWORD, etc.)
# - MQTT settings (MQTT_PASSWORD)
```

### 2. Database Migration
```bash
php artisan migrate
```

### 3. Start MQTT Service
```bash
# Production MQTT Listener (Recommended)
php mqtt-listener-production.php

# Or Laravel Artisan Command
php artisan mqtt:listen
```

## MQTT Topics

### Heartbeat Flow
- **Request**: `iot/{station_id}/heartbeat/request`
- **Response**: `iot/{station_id}/heartbeat/response`
- **Purpose**: Device status check and server command delivery

### Configuration Flow  
- **Request**: `iot/{station_id}/config/request`
- **Response**: `iot/{station_id}/config/response`
- **Purpose**: Update device timing parameters

### Data Upload Flow
- **Request**: `iot/{station_id}/data/request` 
- **Response**: `iot/{station_id}/data/response`
- **Purpose**: Sensor data collection and storage

## Database Tables
- `device_status` - Device online status and flags
- `device_configurations` - Device timing parameters
- `sensor_readings` - Historical sensor data
- `station_information` - Device registration info

## Configuration
All settings are managed through `.env` file:
- **MQTT_HOST**: MQTT broker hostname
- **MQTT_PORT**: MQTT broker port (typically 1883 or 41071)
- **MQTT_USERNAME/PASSWORD**: Authentication credentials
- **DB_***: Database connection settings

## Production Deployment
1. Set `APP_ENV=production` in `.env`
2. Configure Railway database credentials
3. Run `php artisan migrate --force`
4. Start service: `php mqtt-listener-production.php`
5. Consider setting up as system service for auto-restart

## Monitoring
The service provides real-time logging:
- Connection status
- Message reception/transmission
- Database operations
- Error conditions

## Security
- API key validation for all requests
- Database prepared statements
- Environment variable protection
- No sensitive data in repository