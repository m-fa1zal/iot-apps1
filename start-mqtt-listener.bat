@echo off
echo Starting Laravel MQTT Listener...
echo Press Ctrl+C to stop the listener
echo.

cd /d "D:\laragon\www\iot-apps1"
php artisan mqtt:listen

pause