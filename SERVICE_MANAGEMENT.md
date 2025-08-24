# IoT Apps Service Management

## 🚀 Quick Start

**Start All Services:**
```bash
start-all-services.bat
```

**Stop All Services:**
```bash
stop-all-services.bat
```

**Check Service Status:**
```bash
check-services-status.bat
```

## 📋 Services Overview

The IoT application consists of 4 main services:

| Service | Port | Purpose | Command |
|---------|------|---------|---------|
| **MQTT Broker** | 1883 | Message broker for IoT devices | `mosquitto.exe -c mosquitto.conf -v` |
| **Laravel HTTP** | 8000 | Web application backend | `php artisan serve --port=8000` |
| **HTTPS Proxy** | 8443 | SSL/TLS frontend proxy | `local-ssl-proxy --source 8443 --target 8000` |
| **MQTT Listener** | - | Laravel MQTT message processor | `php artisan mqtt:listen` |

## 🌐 Access URLs

- **HTTPS (Recommended)**: https://localhost:8443
- **HTTP (Development)**: http://localhost:8000
- **MQTT**: mqtt://localhost:1883

## 📝 Service Management Scripts

### Main Scripts
- `start-all-services.bat` - Starts all 4 services in separate windows
- `stop-all-services.bat` - Stops all services and cleans up processes
- `check-services-status.bat` - Shows status of all services and ports

### Legacy Scripts (Redirected)
- `start-mosquitto-broker.bat` - Now redirects to consolidated script
- `start-mqtt-listener.bat` - Now redirects to consolidated script  
- `start-https.bat` - Now redirects to consolidated script

## 🔧 Manual Service Control

### Start Individual Services

**MQTT Broker:**
```bash
"C:\Program Files\mosquitto\mosquitto.exe" -c mosquitto.conf -v
```

**Laravel Server:**
```bash
cd D:\laragon\www\iot-apps1
php artisan serve --port=8000
```

**HTTPS Proxy:**
```bash
cd D:\laragon\www\iot-apps1
local-ssl-proxy --source 8443 --target 8000 --cert ssl/localhost.crt --key ssl/localhost.key
```

**MQTT Listener:**
```bash
cd D:\laragon\www\iot-apps1
php artisan mqtt:listen
```

## 🛠️ Troubleshooting

### Port Conflicts
If you get "port already in use" errors:
1. Run `stop-all-services.bat`
2. Wait 5 seconds
3. Run `start-all-services.bat`

### Service Not Starting
1. Check `check-services-status.bat` for detailed status
2. Look at the individual service windows for error messages
3. Ensure all dependencies are installed:
   - Mosquitto MQTT broker
   - PHP (Laravel)
   - Node.js (for HTTPS proxy)

### SSL Certificate Issues
If HTTPS doesn't work:
1. Check that `ssl/localhost.crt` and `ssl/localhost.key` exist
2. Browser will show security warning for self-signed certificates
3. Click "Advanced" → "Proceed to localhost" to continue

## 📦 Dependencies

Ensure these are installed:
- **Mosquitto MQTT Broker** (`C:\Program Files\mosquitto\mosquitto.exe`)
- **PHP** (for Laravel)
- **Node.js & npm** (for HTTPS proxy)
- **local-ssl-proxy** (`npm install -g local-ssl-proxy`)

## 🔄 Development Workflow

1. **Daily startup**: Run `start-all-services.bat`
2. **Development**: Access app at `https://localhost:8443`
3. **Check status**: Use `check-services-status.bat` if needed
4. **Daily shutdown**: Run `stop-all-services.bat` or close all service windows