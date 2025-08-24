#!/bin/bash

echo "======================================="
echo "IoT Apps - Railway Startup Script"
echo "======================================="

# Function to handle cleanup on exit
cleanup() {
    echo "Cleaning up processes..."
    pkill -f mosquitto || true
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

# Start all services simultaneously in background
echo "Starting all services simultaneously..."

# 1. Start MQTT Broker
echo "[1/3] Starting MQTT Broker..."
mosquitto -c mosquitto.conf &
BROKER_PID=$!

# 2. Start Laravel MQTT Listener  
echo "[2/3] Starting Laravel MQTT Listener..."
php artisan mqtt:listen &
LISTENER_PID=$!

# 3. Start Laravel Web Server
echo "[3/3] Starting Laravel Web Server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000} &
WEB_PID=$!

echo "======================================="
echo "All services started!"
echo "MQTT Broker PID: $BROKER_PID"
echo "MQTT Listener PID: $LISTENER_PID" 
echo "Web Server PID: $WEB_PID"
echo "======================================="

# Wait for all background processes
wait