@echo off
echo ======================================
echo NOTICE: This script has been consolidated
echo ======================================
echo.
echo This functionality is now available in:
echo start-all-services.bat
echo.
echo The new consolidated script starts:
echo - Mosquitto MQTT Broker
echo - Laravel HTTP Server  
echo - HTTPS Proxy
echo - Laravel MQTT Listener
echo.
echo Would you like to run the consolidated script? (Y/N)
set /p choice="Choice: "
if /i "%choice%"=="Y" (
    echo.
    echo Starting consolidated services...
    call start-all-services.bat
) else (
    echo.
    echo You can run it manually with: start-all-services.bat
)
pause