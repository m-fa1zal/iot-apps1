#!/bin/bash

# Deploy multiple Railway services for Laravel IoT Application
# This script automates the deployment of web, mqtt, and worker services

set -e

echo "🚂 Deploying Laravel IoT Application to Railway..."
echo "This will create 3 separate services: web, mqtt, and worker"

# Check if railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "❌ Railway CLI not found. Please install it first:"
    echo "npm install -g @railway/cli"
    exit 1
fi

# Login check
if ! railway whoami &> /dev/null; then
    echo "🔐 Please login to Railway first:"
    railway login
fi

# Create project if needed
echo "📁 Initializing Railway project..."
railway init

# Deploy Web Service
echo "🌐 Deploying Web Service..."
railway service create web
cp railway-web.json railway.json
railway up --service web

# Set environment variables for web service
echo "⚙️  Setting environment variables for web service..."
railway variables set RAILWAY_SERVICE_NAME=web --service web

# Deploy MQTT Service  
echo "📡 Deploying dedicated MQTT Listener Service..."
railway service create mqtt
cp railway-mqtt.json railway.json
railway up --service mqtt

# Set environment variables for MQTT service
echo "⚙️  Setting environment variables for MQTT service..."
railway variables set RAILWAY_SERVICE_NAME=mqtt --service mqtt
railway variables set MQTT_HOST="your-mqtt-broker-host" --service mqtt
railway variables set MQTT_PORT="1883" --service mqtt
railway variables set MQTT_USERNAME="root" --service mqtt
railway variables set MQTT_PASSWORD="your-mqtt-password" --service mqtt

# Deploy Worker Service
echo "⚙️  Deploying Worker Service..."
railway service create worker
cp railway-worker.json railway.json
railway up --service worker

# Set environment variables for worker service
echo "⚙️  Setting environment variables for worker service..."
railway variables set RAILWAY_SERVICE_NAME=worker --service worker

# Restore original railway.json
cp railway-web.json railway.json

echo "✅ Deployment complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update MQTT broker settings in Railway dashboard for the 'mqtt' service:"
echo "   - MQTT_HOST: your actual MQTT broker host"
echo "   - MQTT_PASSWORD: your actual MQTT broker password"
echo "2. Set up your database connection in Railway dashboard"
echo "3. Set APP_KEY and other environment variables for all services"
echo "4. Check service logs: railway logs --service [web|mqtt|worker]"
echo ""
echo "🔗 Service URLs:"
echo "- Web: Check Railway dashboard for the generated URL"
echo "- MQTT: Dedicated listener service (processes IoT messages)"
echo "- Worker: Background job processor (no public URL)"
echo ""
echo "📡 MQTT Service will now process messages from topics: iot/*"