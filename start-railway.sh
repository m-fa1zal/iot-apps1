#!/bin/bash

echo "======================================="
echo "IoT Apps - Railway Startup Script"
echo "======================================="

# Function to handle cleanup on exit
cleanup() {
    echo "Cleaning up processes..."
    pkill -f "artisan mqtt:listen" || true
    pkill -f "artisan serve" || true
    exit 0
}

# Trap signals for graceful shutdown
trap cleanup SIGTERM SIGINT

# Database setup for Railway deployment
echo "Setting up database..."
php artisan migrate --force

# Check if database is empty and seed if needed
if [ "$(php artisan tinker --execute="echo \Illuminate\Support\Facades\DB::table('users')->count();")" = "0" ]; then
    echo "Database is empty, running seeders..."
    php artisan db:seed --force
else
    echo "Database already seeded, skipping seeders..."
fi

# Laravel optimization commands for production
echo "Optimizing Laravel for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start services (using Railway's external MQTT broker)
echo "Starting services..."

# 1. Start Laravel Web Server
echo "[1/2] Starting Laravel Web Server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000} &
WEB_PID=$!

# Debug MQTT connection before starting listener
echo "Debugging MQTT connection..."
echo "Testing connection to MQTT broker..."

# Test if we can reach the MQTT broker
if command -v nc >/dev/null 2>&1; then
    echo "Testing TCP connection to MQTT broker..."
    timeout 10 nc -zv ${MQTT_HOST:-localhost} ${MQTT_PORT:-1883} || echo "❌ Cannot reach MQTT broker"
else
    echo "⚠️ netcat not available for connection testing"
fi

# Run MQTT connection test first
echo "Running MQTT connection test..."
php test-mqtt-connection.php

# 2. Start Laravel MQTT Listener (connects to Railway's MQTT broker)
echo "[2/2] Starting Laravel MQTT Listener..."
echo "MQTT Config: ${MQTT_HOST:-not_set}:${MQTT_PORT:-not_set}"
php artisan mqtt:listen &
LISTENER_PID=$!

echo "======================================="
echo "All services started!"
echo "Web Server PID: $WEB_PID"
echo "MQTT Listener PID: $LISTENER_PID" 
echo "Using Railway's external MQTT broker"
echo "======================================="

# Wait for all background processes
wait