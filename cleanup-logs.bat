@echo off
echo Cleaning up large log files...

echo Current log file size:
dir "D:\laragon\www\iot-apps1\storage\logs\laravel.log" | find "laravel.log"

echo.
echo Backing up current log file...
copy "D:\laragon\www\iot-apps1\storage\logs\laravel.log" "D:\laragon\www\iot-apps1\storage\logs\laravel_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%.log"

echo.
echo Clearing current log file...
echo. > "D:\laragon\www\iot-apps1\storage\logs\laravel.log"

echo.
echo Log cleanup completed!
echo New log file size:
dir "D:\laragon\www\iot-apps1\storage\logs\laravel.log" | find "laravel.log"

echo.
echo Press any key to exit...
pause > nul