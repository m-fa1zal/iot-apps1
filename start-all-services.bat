@echo off
title IoT Apps - All Services Startup
echo ======================================
echo IoT Apps - Starting All Services
echo ======================================
echo.

echo Services to start:
echo [1] Mosquitto MQTT Broker (Port 1883)
echo [2] Laravel HTTP Server (Port 8000) 
echo [3] HTTPS Proxy (Port 8443)
echo [4] Laravel MQTT Listener
echo.

echo ======================================
echo Starting services in sequence...
echo ======================================
echo.

REM Change to project directory
cd /d "D:\laragon\www\iot-apps1"

REM Clear any cached config
echo Clearing Laravel configuration cache...
php artisan config:clear >nul 2>&1
echo.

REM Step 1: Start Mosquitto MQTT Broker
echo [1/4] Starting Mosquitto MQTT Broker on port 1883...
start "MQTT Broker" cmd /k "title MQTT Broker && echo Mosquitto MQTT Broker && echo Press Ctrl+C to stop && echo. && "C:\Program Files\mosquitto\mosquitto.exe" -c mosquitto.conf -v"

REM Wait a moment for MQTT broker to start
timeout /t 2 /nobreak >nul

REM Step 2: Start Laravel HTTP Server
echo [2/4] Starting Laravel HTTP server on port 8000...
start "Laravel Server" cmd /k "title Laravel HTTP Server && cd /d "D:\laragon\www\iot-apps1" && php artisan serve --port=8000"

REM Wait for Laravel to start
timeout /t 3 /nobreak >nul

REM Step 3: Start HTTPS Proxy
echo [3/4] Starting HTTPS proxy on port 8443...
start "HTTPS Proxy" cmd /k "title HTTPS Proxy && cd /d "D:\laragon\www\iot-apps1" && echo HTTPS Proxy: https://localhost:8443 ^-^> http://localhost:8000 && echo Press Ctrl+C to stop && echo. && local-ssl-proxy --source 8443 --target 8000 --cert ssl/localhost.crt --key ssl/localhost.key"

REM Wait for HTTPS proxy to start
timeout /t 3 /nobreak >nul

REM Step 4: Start MQTT Listener
echo [4/4] Starting Laravel MQTT Listener...
start "MQTT Listener" cmd /k "title Laravel MQTT Listener && cd /d "D:\laragon\www\iot-apps1" && echo Laravel MQTT Listener && echo Press Ctrl+C to stop && echo. && php artisan mqtt:listen"

echo.
echo ======================================
echo All services started successfully!
echo ======================================
echo.

echo Service URLs:
echo 🌐 HTTP:  http://localhost:8000
echo 🔒 HTTPS: https://localhost:8443
echo 📡 MQTT:  mqtt://localhost:1883
echo.

echo Service Windows:
echo [1] MQTT Broker     - Mosquitto message broker
echo [2] Laravel Server  - Web application HTTP server
echo [3] HTTPS Proxy     - SSL/TLS proxy for HTTPS access
echo [4] MQTT Listener   - Laravel background MQTT processor
echo.

echo Tips:
echo - Access your app at: https://localhost:8443
echo - Check service status with: verify-https.bat
echo - Close individual windows to stop specific services
echo - Or close all windows to stop everything
echo.

echo Press any key to close this startup window...
pause >nul