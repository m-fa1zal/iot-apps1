@echo off
echo ======================================
echo IoT Apps - HTTPS Testing Solutions
echo ======================================
echo.

echo Current Status:
echo [✓] Laravel app running on HTTP: http://127.0.0.1:8000
echo [❌] Apache SSL not working (connection refused)
echo.

echo Available Solutions:
echo.
echo 1. Quick HTTP Test (Change APP_URL temporarily)
echo    - Edit .env: APP_URL=http://localhost:8000
echo    - Test: http://localhost:8000
echo.
echo 2. HTTPS Proxy (Recommended)
echo    - Install: npm install -g local-ssl-proxy
echo    - Run: local-ssl-proxy --source 8443 --target 8000 --cert ssl/localhost.crt --key ssl/localhost.key
echo    - Test: https://localhost:8443
echo.
echo 3. Fix Laragon Apache SSL
echo    - Check Laragon control panel for errors
echo    - Look at C:\laragon\logs\apache_error.log
echo    - Verify Apache starts without SSL first
echo.

echo ======================================
echo Choose your preferred method and follow the instructions
echo in https-proxy-setup.md for detailed steps.
echo ======================================
pause