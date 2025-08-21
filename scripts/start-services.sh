#!/bin/bash

# Railway Startup Script for Laravel IoT Application
# This script initializes Laravel and starts the MQTT listener

set -e  # Exit on any error

echo "🚀 Starting Railway deployment for Laravel IoT Application..."

# Function to log with timestamp
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1"
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

log "📋 Checking environment..."

# Check PHP version
if command_exists php; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    log "✅ PHP version: $PHP_VERSION"
else
    log "❌ PHP not found!"
    exit 1
fi

# Check Composer
if command_exists composer; then
    log "✅ Composer found"
else
    log "❌ Composer not found!"
    exit 1
fi

log "📦 Installing/updating dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

log "🔧 Setting up Laravel application..."

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    log "🔑 Generating application key..."
    php artisan key:generate --force
else
    log "✅ Application key already exists"
fi

# Run database migrations
log "🗄️  Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
log "🧹 Clearing and caching configurations..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configurations for production
log "⚡ Caching configurations for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if it doesn't exist
if [ ! -L "public/storage" ]; then
    log "🔗 Creating storage symlink..."
    php artisan storage:link
fi

# Set proper permissions
log "🔒 Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

log "✅ Laravel application setup completed!"

# Check which service to start based on environment variable
case "${RAILWAY_SERVICE_NAME:-web}" in
    "web")
        log "🌐 Starting Laravel web server..."
        exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
        ;;
    "mqtt")
        log "📡 Starting MQTT listener service..."
        exec php artisan mqtt:listen
        ;;
    "worker")
        log "⚙️  Starting queue worker..."
        exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600
        ;;
    *)
        log "❌ Unknown service: ${RAILWAY_SERVICE_NAME}"
        log "Available services: web, mqtt, worker"
        exit 1
        ;;
esac