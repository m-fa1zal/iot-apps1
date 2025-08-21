@echo off
echo Starting Mosquitto MQTT Broker...
echo Press Ctrl+C to stop the broker
echo.

mosquitto -c mosquitto.conf -v

pause