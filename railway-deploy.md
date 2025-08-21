# Railway Deployment Guide for Laravel IoT Application

This guide explains how to deploy the Laravel IoT application to Railway with automatic Laravel and MQTT listener startup.

## Deployment Options

### Option 1: Multiple Services (Recommended)

Deploy as separate services for better resource management:

1. **Web Service**: Handles HTTP requests
2. **MQTT Service**: Runs the MQTT listener
3. **Worker Service**: Processes background jobs

#### Setup Steps:

1. **Create Railway Project**
   ```bash
   railway login
   railway new
   ```

2. **Deploy Web Service**
   ```bash
   railway service create web
   railway up
   ```

3. **Deploy MQTT Service**
   ```bash
   railway service create mqtt
   railway up
   ```

4. **Set Environment Variables**
   - `RAILWAY_SERVICE_NAME=web` (for web service)
   - `RAILWAY_SERVICE_NAME=mqtt` (for MQTT service)
   - `RAILWAY_SERVICE_NAME=worker` (for worker service)

### Option 2: Single Service with Supervisor

Deploy as a single service using Supervisor to manage processes:

1. **Use Docker Deployment**
   - Railway will automatically detect `Dockerfile.railway`
   - All services run in one container

2. **Use Nixpacks with Procfile**
   - Railway uses the `Procfile` for process management

## Environment Variables

Set these in Railway dashboard:

```bash
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-app-key

# Database
DB_CONNECTION=mysql
DB_HOST=your-railway-mysql-host
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-password

# MQTT
MQTT_HOST=your-mqtt-broker
MQTT_PORT=1883
MQTT_USERNAME=your-username
MQTT_PASSWORD=your-password

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=7
```

## Files Included

### Core Files
- `railway.json` - Default Railway configuration  
- `railway-web.json` - Web service configuration
- `railway-mqtt.json` - MQTT service configuration
- `railway-worker.json` - Worker service configuration
- `railway-start.sh` - Main startup script
- `deploy-services.sh` - Automated deployment script

### Docker Option
- `docker/Dockerfile.railway` - Railway-optimized Dockerfile
- `docker/nginx.conf` - Nginx configuration
- `docker/start.sh` - Docker startup script
- `supervisor/laravel-mqtt.conf` - Supervisor configuration

### Scripts
- `scripts/start-services.sh` - Enhanced startup script with logging

## Deployment Commands

### Automated Multi-Service Deployment
```bash
# Use the automated deployment script
chmod +x deploy-services.sh
./deploy-services.sh
```

### Manual Multi-Service Deployment
```bash
# 1. Create and deploy web service
railway service create web
cp railway-web.json railway.json
railway up --service web
railway variables set RAILWAY_SERVICE_NAME=web --service web

# 2. Create and deploy MQTT service  
railway service create mqtt
cp railway-mqtt.json railway.json
railway up --service mqtt
railway variables set RAILWAY_SERVICE_NAME=mqtt --service mqtt

# 3. Create and deploy worker service
railway service create worker
cp railway-worker.json railway.json  
railway up --service worker
railway variables set RAILWAY_SERVICE_NAME=worker --service worker
```

### Service Management
```bash
# View logs for specific service
railway logs --service web
railway logs --service mqtt
railway logs --service worker

# Connect to specific service shell
railway shell --service web
```

## Monitoring

### Check Service Status
```bash
# View all services
railway status

# View specific service logs
railway logs --service mqtt
railway logs --service web
```

### MQTT Listener Health Check
The MQTT listener includes automatic reconnection and health monitoring. Check logs for:
- Connection status
- Message processing
- Error handling

## Troubleshooting

### Common Issues

1. **MQTT Connection Failed**
   - Check MQTT broker credentials
   - Verify network connectivity
   - Review firewall settings

2. **Database Connection Issues**
   - Verify database credentials
   - Check Railway MySQL service status
   - Ensure proper environment variables

3. **Laravel Setup Errors**
   - Check APP_KEY is set
   - Verify storage permissions
   - Review migration status

### Debug Commands
```bash
# Check Laravel status
railway run php artisan about

# Test MQTT connection
railway run php artisan mqtt:test

# View queue status
railway run php artisan queue:failed
```

## Performance Optimization

### Resource Allocation
- Web Service: 1GB RAM, 1 vCPU
- MQTT Service: 512MB RAM, 0.5 vCPU
- Worker Service: 512MB RAM, 0.5 vCPU

### Scaling
- Use horizontal scaling for web service
- Keep MQTT service as single instance
- Scale workers based on job queue load

## Security

### Best Practices
- Use environment variables for secrets
- Enable HTTPS in production
- Implement proper CORS settings
- Regular security updates

### Network Security
- Restrict MQTT broker access
- Use secure database connections
- Implement rate limiting