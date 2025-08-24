@echo off
echo Starting Mosquitto MQTT Broker...
echo Press Ctrl+C to stop the broker
echo.

"C:\Program Files\mosquitto\mosquitto.exe" -c mosquitto.conf -v

pause