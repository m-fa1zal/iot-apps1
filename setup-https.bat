@echo off
echo ======================================
echo IoT Apps - HTTPS Setup Helper
echo ======================================
echo.

echo Current setup status:
echo [✓] SSL Certificate created (ssl/localhost.crt)
echo [✓] SSL Private Key created (ssl/localhost.key)
echo [✓] Apache Virtual Host config created (apache-ssl-vhost.conf)
echo [✓] Laravel configured for HTTPS
echo.

echo Next steps to complete HTTPS setup:
echo.
echo 1. Right-click Laragon tray icon
echo 2. Go to Apache ^> httpd.conf
echo 3. Uncomment these lines (remove # at the beginning):
echo    LoadModule ssl_module modules/mod_ssl.so
echo    Include conf/extra/httpd-ssl.conf
echo.
echo 4. Right-click Laragon tray icon
echo 5. Go to Apache ^> sites-enabled
echo 6. Create new file: iot-apps1-ssl.conf
echo 7. Copy contents from apache-ssl-vhost.conf to the new file
echo.
echo 8. Restart Laragon (Stop All, then Start All)
echo.
echo 9. Access your app at: https://localhost
echo    (Accept security warning for self-signed certificate)
echo.

echo ======================================
echo For detailed instructions, see: ssl-setup.md
echo ======================================
pause