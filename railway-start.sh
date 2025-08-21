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
if [ "$RAILWAY_SERVICE_NAME" = "iot-apps1" ]; then
    echo "📡 Starting MQTT listener..."
    exec php artisan mqtt:listen
elif [ "$RAILWAY_SERVICE_NAME" = "worker" ]; then
    echo "⚙️  Starting queue worker..."
    exec php artisan queue:work --sleep=3 --tries=3
else
    echo "🌐 Starting web server..."
    exec php artisan serve --host=0.0.0.0 --port=$PORT
fi