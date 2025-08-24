@echo off
title IoT Apps - Stop All Services
echo ======================================
echo IoT Apps - Stopping All Services  
echo ======================================
echo.

echo Stopping services...
echo.

REM Stop processes by window title
echo Stopping MQTT Broker...
taskkill /FI "WINDOWTITLE eq MQTT Broker*" /F >nul 2>&1

echo Stopping Laravel HTTP Server...
taskkill /FI "WINDOWTITLE eq Laravel HTTP Server*" /F >nul 2>&1

echo Stopping HTTPS Proxy...
taskkill /FI "WINDOWTITLE eq HTTPS Proxy*" /F >nul 2>&1

echo Stopping Laravel MQTT Listener...
taskkill /FI "WINDOWTITLE eq Laravel MQTT Listener*" /F >nul 2>&1

REM Also try stopping by process name as fallback
echo.
echo Cleaning up any remaining processes...
taskkill /IM mosquitto.exe /F >nul 2>&1
taskkill /IM php.exe /F >nul 2>&1
taskkill /IM node.exe /F >nul 2>&1

echo.
echo ======================================
echo All IoT services have been stopped.
echo ======================================
echo.

echo Ports released:
echo - Port 1883 (MQTT)
echo - Port 8000 (HTTP)  
echo - Port 8443 (HTTPS)
echo.

echo You can now restart services with:
echo start-all-services.bat
echo.

pause