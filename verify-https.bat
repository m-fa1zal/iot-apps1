@echo off
echo ======================================
echo HTTPS Setup Verification
echo ======================================
echo.

echo Checking services...
echo.

echo Checking Laravel HTTP server (port 8000):
netstat -ano | findstr :8000
if %errorlevel% == 0 (
    echo [✓] Laravel HTTP server is running
) else (
    echo [❌] Laravel HTTP server not running
)

echo.
echo Checking HTTPS proxy (port 8443):
netstat -ano | findstr :8443
if %errorlevel% == 0 (
    echo [✓] HTTPS proxy is running
) else (
    echo [❌] HTTPS proxy not running
)

echo.
echo ======================================
echo If both services are running:
echo.
echo 🌐 HTTP Access:  http://localhost:8000
echo 🔒 HTTPS Access: https://localhost:8443
echo.
echo Note: Browser will show security warning for
echo self-signed certificate - click "Advanced" then
echo "Proceed to localhost" to continue.
echo ======================================
pause