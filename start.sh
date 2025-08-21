#!/bin/bash

echo "🚀 Starting Laravel IoT Application..."

# Generate key if needed
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run package discovery
php artisan package:discover --ansi

# Clear and cache config for production
php artisan config:clear
php artisan config:cache

# Check service type and start appropriate service
if [ "$RAILWAY_SERVICE_NAME" = "iot-apps1" ]; then
    echo "📡 Starting MQTT listener..."
    exec php artisan mqtt:listen
else
    echo "🌐 Starting web server..."  
    exec php artisan serve --host=0.0.0.0 --port=$PORT
fi