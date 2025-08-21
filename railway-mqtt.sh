#!/bin/bash

# Dedicated Railway MQTT Client Startup Script
# Optimized for reliability, auto-restart, and monitoring

set -e

# Configuration
MAX_RESTART_ATTEMPTS=10
RESTART_DELAY=5
HEALTH_CHECK_INTERVAL=30
CONNECTION_TIMEOUT=10

echo "🔌 MQTT Client Service - Railway Deployment"
echo "============================================"

# Function to log with timestamp
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Function to check MQTT connectivity
check_mqtt_connection() {
    log "🔍 Checking MQTT broker connectivity..."
    
    # Verify required MQTT environment variables
    if [[ -z "$MQTT_HOST" || -z "$MQTT_PORT" || -z "$MQTT_USERNAME" ]]; then
        log "❌ Missing required MQTT environment variables"
        log "   Required: MQTT_HOST, MQTT_PORT, MQTT_USERNAME"
        return 1
    fi
    
    log "✅ MQTT environment variables validated"
    return 0
}

# Function to setup Laravel environment
setup_laravel() {
    log "🔧 Setting up Laravel environment..."
    
    # Install dependencies
    log "📦 Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Generate key if needed
    if [ -z "$APP_KEY" ]; then
        log "🔑 Generating application key..."
        php artisan key:generate --force
    fi
    
    # Run migrations (only if database is available)
    if [ -n "$DATABASE_URL" ] || [ -n "$DB_CONNECTION" ]; then
        log "🗄️  Running database migrations..."
        php artisan migrate --force || log "⚠️  Database migrations failed, continuing..."
    fi
    
    # Clear caches
    log "🧹 Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Cache for production
    log "⚡ Caching for production..."
    php artisan config:cache
    
    # Set permissions
    log "🔒 Setting permissions..."
    chmod -R 755 storage bootstrap/cache 2>/dev/null || true
    
    log "✅ Laravel setup complete"
}

# Function to start MQTT listener with monitoring
start_mqtt_listener() {
    local attempt=1
    
    while [ $attempt -le $MAX_RESTART_ATTEMPTS ]; do
        log "📡 Starting MQTT listener (attempt $attempt/$MAX_RESTART_ATTEMPTS)"
        log "🔧 MQTT Configuration:"
        log "   - Host: ${MQTT_HOST}"
        log "   - Port: ${MQTT_PORT}"
        log "   - Username: ${MQTT_USERNAME}"
        log "   - Client ID: ${MQTT_CLIENT_ID:-iot-railway-mqtt-$(date +%s)}"
        log "   - Topics: iot/+/heartbeat/request, iot/+/config/request, iot/+/data/request"
        
        # Start MQTT listener in background with PID tracking
        php artisan mqtt:listen --verbose &
        MQTT_PID=$!
        
        log "📊 MQTT listener started with PID: $MQTT_PID"
        
        # Monitor the process
        monitor_mqtt_process $MQTT_PID
        
        # If we get here, the process died
        log "💀 MQTT listener process died"
        
        if [ $attempt -eq $MAX_RESTART_ATTEMPTS ]; then
            log "❌ Maximum restart attempts reached. Exiting."
            exit 1
        fi
        
        log "⏳ Waiting ${RESTART_DELAY}s before restart..."
        sleep $RESTART_DELAY
        
        # Exponential backoff
        RESTART_DELAY=$((RESTART_DELAY * 2))
        if [ $RESTART_DELAY -gt 60 ]; then
            RESTART_DELAY=60
        fi
        
        attempt=$((attempt + 1))
    done
}

# Function to monitor MQTT process health
monitor_mqtt_process() {
    local pid=$1
    local last_health_check=$(date +%s)
    
    while kill -0 $pid 2>/dev/null; do
        current_time=$(date +%s)
        
        # Periodic health check
        if [ $((current_time - last_health_check)) -ge $HEALTH_CHECK_INTERVAL ]; then
            log "💓 Health check - MQTT listener running (PID: $pid)"
            
            # Check memory usage
            if command -v ps >/dev/null 2>&1; then
                memory_kb=$(ps -o rss= -p $pid 2>/dev/null || echo "unknown")
                if [ "$memory_kb" != "unknown" ]; then
                    memory_mb=$((memory_kb / 1024))
                    log "📊 Memory usage: ${memory_mb}MB"
                    
                    # Alert if memory usage is too high (>500MB)
                    if [ $memory_mb -gt 500 ]; then
                        log "⚠️  High memory usage detected: ${memory_mb}MB"
                    fi
                fi
            fi
            
            last_health_check=$current_time
        fi
        
        sleep 5
    done
    
    log "💀 MQTT process $pid is no longer running"
}

# Function to handle graceful shutdown
cleanup() {
    log "🛑 Received shutdown signal"
    
    if [ -n "$MQTT_PID" ]; then
        log "🔄 Stopping MQTT listener (PID: $MQTT_PID)..."
        kill -TERM $MQTT_PID 2>/dev/null || true
        
        # Wait for graceful shutdown
        local timeout=10
        while [ $timeout -gt 0 ] && kill -0 $MQTT_PID 2>/dev/null; do
            sleep 1
            timeout=$((timeout - 1))
        done
        
        # Force kill if still running
        if kill -0 $MQTT_PID 2>/dev/null; then
            log "⚠️  Force killing MQTT listener..."
            kill -KILL $MQTT_PID 2>/dev/null || true
        fi
    fi
    
    log "✅ Shutdown complete"
    exit 0
}

# Set up signal handlers
trap cleanup SIGTERM SIGINT SIGQUIT

# Main execution
main() {
    log "🚀 Initializing MQTT Client Service..."
    
    # Validate MQTT connection parameters
    if ! check_mqtt_connection; then
        log "❌ MQTT connection check failed"
        exit 1
    fi
    
    # Setup Laravel
    setup_laravel
    
    # Start MQTT listener with monitoring
    start_mqtt_listener
}

# Execute main function
main "$@"