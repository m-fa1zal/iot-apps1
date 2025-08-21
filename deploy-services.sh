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
echo "📡 Deploying MQTT Service..."
railway service create mqtt
cp railway-mqtt.json railway.json
railway up --service mqtt

# Set environment variables for MQTT service
echo "⚙️  Setting environment variables for MQTT service..."
railway variables set RAILWAY_SERVICE_NAME=mqtt --service mqtt

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
echo "1. Set up your database connection in Railway dashboard"
echo "2. Configure MQTT broker settings"
echo "3. Set APP_KEY and other environment variables"
echo "4. Check service logs: railway logs --service [web|mqtt|worker]"
echo ""
echo "🔗 Service URLs:"
echo "- Web: Check Railway dashboard for the generated URL"
echo "- MQTT & Worker: Background services (no public URLs)"