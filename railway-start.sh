#!/bin/bash

# Railway-specific startup script for Laravel IoT Application
# This script is called by Railway during deployment

set -e

echo "🚂 Railway startup script initiated..."

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Laravel setup
echo "🔧 Setting up Laravel..."

# Generate key if needed
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache

# Storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "✅ Laravel setup complete!"

# Start the appropriate service based on Railway service name
if [ "$RAILWAY_SERVICE_NAME" = "mqtt" ]; then
    echo "📡 Starting dedicated MQTT listener service..."
    echo "🔧 MQTT Service Configuration:"
    echo "   - Host: ${MQTT_HOST:-localhost}"
    echo "   - Port: ${MQTT_PORT:-1883}" 
    echo "   - Username: ${MQTT_USERNAME:-root}"
    echo "   - Topics: Listening to all iot/* topics"
    echo "📊 Starting MQTT listener with enhanced logging..."
    exec php artisan mqtt:listen --verbose
elif [ "$RAILWAY_SERVICE_NAME" = "worker" ]; then
    echo "⚙️  Starting dedicated queue worker service..."
    echo "🔧 Worker Configuration:"
    echo "   - Queue connection: ${QUEUE_CONNECTION:-database}"
    echo "   - Sleep: 3 seconds between jobs"
    echo "   - Max tries: 3 attempts per job"
    echo "📊 Starting queue worker with enhanced logging..."
    exec php artisan queue:work --sleep=3 --tries=3 --verbose
else
    echo "🌐 Starting dedicated web server service..."
    echo "🔧 Web Server Configuration:"
    echo "   - Host: 0.0.0.0"
    echo "   - Port: ${PORT:-8000}"
    echo "   - Environment: ${APP_ENV:-production}"
    echo "📊 Starting web server..."
    exec php artisan serve --host=0.0.0.0 --port=$PORT
fi