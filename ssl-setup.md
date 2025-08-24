# HTTPS Configuration for IoT Apps Laravel Project

## Overview
This document provides instructions to configure HTTPS for your Laravel IoT application running on Laragon.

## Configuration Changes Made
✅ Updated `.env` file: `APP_URL=https://localhost`
✅ Updated `.env.example` file: `APP_URL=https://localhost`  
✅ Updated `config/app.php`: Default URL changed to HTTPS
✅ Updated `config/mail.php`: Mail domain parsing updated for HTTPS
✅ Updated `doc/API_TEST.md`: All URLs changed to HTTPS

## Laragon HTTPS Setup

### Method 1: Manual SSL Configuration (Since automatic SSL options not available)

✅ **SSL Certificate Created**: `ssl/localhost.crt` and `ssl/localhost.key`
✅ **Virtual Host Config Created**: `apache-ssl-vhost.conf`

**Step 1: Enable SSL Module in Laragon Apache**
1. Right-click Laragon tray icon
2. Go to **Apache** > **httpd.conf**
3. Find and uncomment these lines (remove # at the beginning):
   ```apache
   LoadModule ssl_module modules/mod_ssl.so
   Include conf/extra/httpd-ssl.conf
   ```

**Step 2: Configure Virtual Host**
1. Right-click Laragon tray icon  
2. Go to **Apache** > **sites-enabled**
3. Create new file: `iot-apps1-ssl.conf`
4. Copy contents from `apache-ssl-vhost.conf` to the new file
5. Save and close

**Step 3: Restart Apache**
1. Right-click Laragon tray icon
2. Click **Stop All**
3. Click **Start All**

**Step 4: Add to Windows Hosts File (Optional)**
1. Open `C:\Windows\System32\drivers\etc\hosts` as Administrator
2. Add line: `127.0.0.1 iot-apps1.test`
3. Save file

**Step 5: Access Your Application**
- https://localhost (with port if needed)
- https://iot-apps1.test (if hosts file updated)
- Browser will show security warning for self-signed certificate - click "Advanced" > "Proceed"

### Method 2: Manual SSL Certificate Setup

If you need custom SSL certificates:

1. **Generate SSL Certificate:**
   ```bash
   # Create SSL directory
   mkdir ssl
   cd ssl
   
   # Generate private key
   openssl genrsa -out server.key 2048
   
   # Generate certificate signing request
   openssl req -new -key server.key -out server.csr
   
   # Generate self-signed certificate
   openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt
   ```

2. **Configure Apache for HTTPS:**
   Create or modify virtual host configuration in Laragon:
   ```apache
   <VirtualHost *:443>
       ServerName iot-apps1.test
       DocumentRoot "D:/laragon/www/iot-apps1/public"
       
       SSLEngine on
       SSLCertificateFile "D:/laragon/www/iot-apps1/ssl/server.crt"
       SSLCertificateKeyFile "D:/laragon/www/iot-apps1/ssl/server.key"
       
       <Directory "D:/laragon/www/iot-apps1/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

## Laravel Artisan Serve with HTTPS

If you prefer using `php artisan serve`, you can enable HTTPS:

```bash
# Install Laravel's development SSL package (if not already installed)
composer require --dev laravel/pint

# Serve with HTTPS (requires SSL certificate)
php artisan serve --host=0.0.0.0 --port=8080
```

Note: `artisan serve` doesn't natively support SSL. For development SSL with artisan serve, consider using:
- Laravel Valet (macOS/Linux)
- Laravel Homestead (Vagrant)
- Docker with SSL proxy

## Environment-Specific Configuration

### Development Environment
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=https://localhost:8080
```

### Production Environment
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

## Additional Security Configuration

### Force HTTPS in Laravel

Add to `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\URL;

public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### HTTPS Redirect Middleware

Create middleware to force HTTPS:

```bash
php artisan make:middleware ForceHttps
```

In `app/Http/Middleware/ForceHttps.php`:
```php
public function handle($request, Closure $next)
{
    if (!$request->secure() && app()->environment('production')) {
        return redirect()->secure($request->getRequestUri());
    }
    
    return $next($request);
}
```

## Testing HTTPS Configuration

1. **Test Laravel Application:**
   - Visit: https://localhost:8080 (or your configured domain)
   - Check that all assets load over HTTPS
   - Verify API endpoints work with HTTPS

2. **Test API Endpoints:**
   - Config: `POST https://localhost:8080/api/config`
   - Upload: `POST https://localhost:8080/api/upload`

3. **Test MQTT (if using secure MQTT):**
   - Update MQTT configuration to use TLS/SSL if needed
   - Default MQTT runs on port 1883 (unsecured) or 8883 (secured)

## Browser Security

When using self-signed certificates:
1. Browser will show security warning
2. Click "Advanced" → "Proceed to localhost"
3. Or add certificate to trusted certificates in Windows

## Troubleshooting

### Certificate Issues
- Ensure certificate files have correct permissions
- Check certificate validity dates
- Verify certificate matches the domain name

### Mixed Content Warnings
- Update any hard-coded HTTP URLs to HTTPS
- Check that all assets (CSS, JS, images) load over HTTPS

### Laravel Configuration Issues
- Clear Laravel cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Clear route cache: `php artisan route:clear`

## Next Steps

1. ✅ Files have been updated to use HTTPS
2. ⏳ Configure SSL certificates (choose Method 1 or 2 above)
3. ⏳ Test the application with HTTPS
4. ⏳ Update any external API references to use HTTPS
5. ⏳ Consider security headers and HSTS for production

## Production Considerations

For production deployment:
- Use certificates from trusted CA (Let's Encrypt, etc.)
- Configure proper SSL/TLS settings
- Enable HTTP Strict Transport Security (HSTS)
- Set up automatic certificate renewal
- Configure security headers