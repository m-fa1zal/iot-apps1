@echo off
title IoT Apps - Service Status Check
echo ======================================
echo IoT Apps - Service Status Check
echo ======================================
echo.

echo Checking all service ports...
echo.

echo [1] MQTT Broker (Port 1883):
netstat -ano | findstr :1883 >nul 2>&1
if %errorlevel% == 0 (
    echo    [✓] MQTT Broker is running
    netstat -ano | findstr :1883
) else (
    echo    [❌] MQTT Broker not running
)

echo.
echo [2] Laravel HTTP Server (Port 8000):
netstat -ano | findstr :8000 >nul 2>&1
if %errorlevel% == 0 (
    echo    [✓] Laravel HTTP server is running
    netstat -ano | findstr :8000
) else (
    echo    [❌] Laravel HTTP server not running
)

echo.
echo [3] HTTPS Proxy (Port 8443):
netstat -ano | findstr :8443 >nul 2>&1
if %errorlevel% == 0 (
    echo    [✓] HTTPS Proxy is running
    netstat -ano | findstr :8443
) else (
    echo    [❌] HTTPS Proxy not running
)

echo.
echo [4] Process Check:
echo.
echo Mosquitto processes:
tasklist /FI "IMAGENAME eq mosquitto.exe" 2>nul | findstr mosquitto
if %errorlevel% neq 0 echo    No Mosquitto processes found

echo.
echo PHP processes (Laravel):
tasklist /FI "IMAGENAME eq php.exe" 2>nul | findstr php
if %errorlevel% neq 0 echo    No PHP processes found

echo.
echo Node.js processes (HTTPS Proxy):
tasklist /FI "IMAGENAME eq node.exe" 2>nul | findstr node
if %errorlevel% neq 0 echo    No Node.js processes found

echo.
echo ======================================
echo Service URLs (if running):
echo 🌐 HTTP:  http://localhost:8000
echo 🔒 HTTPS: https://localhost:8443  
echo 📡 MQTT:  mqtt://localhost:1883
echo ======================================
echo.

pause