#!/bin/bash

# Railway Multi-Service Deployment Script
# Automates the deployment of web, mqtt, and worker services

set -e

echo "🚂 Railway Multi-Service Deployment"
echo "=================================="

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "❌ Railway CLI not found. Please install it first:"
    echo "   npm install -g @railway/cli"
    exit 1
fi

# Function to create and deploy a service
deploy_service() {
    local service_name=$1
    local config_file=$2
    
    echo "🔧 Deploying $service_name service..."
    
    # Create service if it doesn't exist
    railway service create "$service_name" 2>/dev/null || echo "   Service $service_name already exists"
    
    # Switch to the service
    railway service use "$service_name"
    
    # Set service-specific environment variable
    railway variables set "RAILWAY_SERVICE_NAME=$service_name"
    
    # Deploy with specific config
    railway up --config "$config_file"
    
    echo "✅ $service_name service deployed successfully"
    echo ""
}

# Function to set common environment variables
set_common_variables() {
    local service_name=$1
    
    echo "🔧 Setting common environment variables for $service_name..."
    
    railway service use "$service_name"
    
    # Laravel Configuration
    railway variables set "APP_ENV=production"
    railway variables set "APP_DEBUG=false"
    
    # Database Configuration
    railway variables set "DB_CONNECTION=mysql"
    railway variables set "QUEUE_CONNECTION=database"
    
    echo "✅ Common variables set for $service_name"
}

# Function to set MQTT-specific variables
set_mqtt_variables() {
    echo "🔧 Setting MQTT-specific environment variables..."
    
    railway service use "mqtt"
    
    # Prompt for MQTT configuration if not provided
    if [ -z "$MQTT_HOST" ]; then
        read -p "Enter MQTT Host: " MQTT_HOST
    fi
    
    if [ -z "$MQTT_PORT" ]; then
        read -p "Enter MQTT Port (default 1883): " MQTT_PORT
        MQTT_PORT=${MQTT_PORT:-1883}
    fi
    
    if [ -z "$MQTT_USERNAME" ]; then
        read -p "Enter MQTT Username: " MQTT_USERNAME
    fi
    
    if [ -z "$MQTT_PASSWORD" ]; then
        read -s -p "Enter MQTT Password: " MQTT_PASSWORD
        echo ""
    fi
    
    # Set MQTT variables
    railway variables set "MQTT_HOST=$MQTT_HOST"
    railway variables set "MQTT_PORT=$MQTT_PORT"
    railway variables set "MQTT_USERNAME=$MQTT_USERNAME"
    railway variables set "MQTT_PASSWORD=$MQTT_PASSWORD"
    railway variables set "MQTT_CLIENT_ID=iot-railway-mqtt-$(date +%s)"
    
    echo "✅ MQTT variables configured"
}

# Main deployment process
main() {
    echo "Starting multi-service deployment..."
    echo ""
    
    # Deploy Web Service
    deploy_service "web" "railway-web.json"
    set_common_variables "web"
    railway variables set "PORT=8000"
    
    # Deploy MQTT Service
    deploy_service "mqtt" "railway-mqtt.json"
    set_common_variables "mqtt"
    set_mqtt_variables
    
    # Ask if user wants worker service
    echo "Do you want to deploy the worker service? (y/n)"
    read -r deploy_worker
    
    if [[ $deploy_worker =~ ^[Yy]$ ]]; then
        deploy_service "worker" "railway-worker.json"
        set_common_variables "worker"
    fi
    
    echo "🎉 Multi-service deployment completed!"
    echo ""
    echo "📋 Next steps:"
    echo "1. Set your APP_KEY for all services:"
    echo "   railway variables set APP_KEY=your-app-key --service web"
    echo "   railway variables set APP_KEY=your-app-key --service mqtt"
    echo "   railway variables set APP_KEY=your-app-key --service worker"
    echo ""
    echo "2. Set your DATABASE_URL for all services:"
    echo "   railway variables set DATABASE_URL=your-db-url --service web"
    echo "   railway variables set DATABASE_URL=your-db-url --service mqtt"
    echo "   railway variables set DATABASE_URL=your-db-url --service worker"
    echo ""
    echo "3. Check service status:"
    echo "   railway status --service web"
    echo "   railway status --service mqtt"
    echo "   railway status --service worker"
    echo ""
    echo "4. View logs:"
    echo "   railway logs --service mqtt --follow"
}

# Execute main function
main "$@"