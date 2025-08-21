# Railway Multi-Service Deployment Setup

## Overview
This setup deploys your Laravel IoT application as separate services on Railway:
- **Web Service**: Handles HTTP requests and serves the web interface
- **MQTT Service**: Dedicated MQTT client for IoT device communication
- **Worker Service**: Background job processing (optional)

## Service Configuration Files

### 1. Web Service (`railway-web.json`)
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "nixpacks",
    "buildCommand": "composer install --no-dev --optimize-autoloader --ignore-platform-reqs && npm install && npm run build"
  },
  "deploy": {
    "numReplicas": 1,
    "sleepApplication": false,
    "restartPolicyType": "always",
    "startCommand": "bash railway-start.sh",
    "healthcheckPath": "/api/health",
    "healthcheckTimeout": 100
  }
}
```

### 2. MQTT Service (`railway-mqtt.json`)
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "nixpacks",
    "buildCommand": "composer install --no-dev --optimize-autoloader --ignore-platform-reqs"
  },
  "deploy": {
    "numReplicas": 1,
    "sleepApplication": false,
    "restartPolicyType": "always",
    "startCommand": "bash railway-mqtt.sh"
  }
}
```

### 3. Worker Service (`railway-worker.json`)
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "nixpacks",
    "buildCommand": "composer install --no-dev --optimize-autoloader --ignore-platform-reqs"
  },
  "deploy": {
    "numReplicas": 1,
    "sleepApplication": false,
    "restartPolicyType": "always",
    "startCommand": "bash railway-start.sh"
  }
}
```

## Deployment Steps

### Step 1: Create Services on Railway
1. **Web Service**:
   ```bash
   railway service create web
   railway service use web
   railway up --config railway-web.json
   ```

2. **MQTT Service**:
   ```bash
   railway service create mqtt
   railway service use mqtt
   railway up --config railway-mqtt.json
   ```

3. **Worker Service** (optional):
   ```bash
   railway service create worker
   railway service use worker
   railway up --config railway-worker.json
   ```

### Step 2: Environment Variables Setup

#### Shared Variables (All Services)
```bash
# Laravel Configuration
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-app-key-here

# Database Configuration
DATABASE_URL=your-database-url
DB_CONNECTION=mysql

# Queue Configuration
QUEUE_CONNECTION=database
```

#### Web Service Specific
```bash
RAILWAY_SERVICE_NAME=web
PORT=8000
```

#### MQTT Service Specific
```bash
RAILWAY_SERVICE_NAME=mqtt
MQTT_HOST=your-mqtt-broker-host
MQTT_PORT=1883
MQTT_USERNAME=your-mqtt-username
MQTT_PASSWORD=your-mqtt-password
MQTT_CLIENT_ID=iot-railway-mqtt
```

#### Worker Service Specific
```bash
RAILWAY_SERVICE_NAME=worker
```

### Step 3: Service Communication
Services can communicate using Railway's internal networking:
- Use service names as hostnames
- Example: `http://web:8000` or `mqtt://mqtt:1883`

## Startup Scripts

### `railway-start.sh` (Multi-purpose)
Automatically detects service type via `RAILWAY_SERVICE_NAME`:
- `web` → Starts Laravel web server
- `mqtt` → Starts MQTT listener
- `worker` → Starts queue worker

### `railway-mqtt.sh` (Dedicated MQTT)
Optimized for MQTT service with:
- Auto-restart on failures (up to 10 attempts)
- Health monitoring every 30 seconds
- Memory usage tracking
- Graceful shutdown handling

## Monitoring & Health Checks

### Web Service
- Health check endpoint: `/api/health`
- Timeout: 100 seconds
- Auto-restart on failures

### MQTT Service
- Process monitoring with PID tracking
- Memory usage alerts (>500MB)
- Automatic restart with exponential backoff
- Connection validation before startup

### Worker Service
- Queue job processing monitoring
- Auto-restart on failures
- Verbose logging enabled

## Scaling Considerations

### Horizontal Scaling
- **Web Service**: Can be scaled to multiple replicas
- **MQTT Service**: Should run single replica (MQTT client connection)
- **Worker Service**: Can be scaled based on queue load

### Resource Allocation
- **Web Service**: Standard web application resources
- **MQTT Service**: Lower CPU, persistent connection
- **Worker Service**: CPU-intensive for background jobs

## Troubleshooting

### Common Issues
1. **MQTT Connection Failures**:
   - Check `MQTT_HOST`, `MQTT_PORT`, `MQTT_USERNAME` variables
   - Verify broker accessibility from Railway

2. **Database Connection Issues**:
   - Ensure `DATABASE_URL` is properly set
   - Check database service availability

3. **Service Communication**:
   - Use internal Railway hostnames
   - Check service names match exactly

### Logs Access
```bash
# View logs for specific service
railway logs --service web
railway logs --service mqtt
railway logs --service worker

# Follow logs in real-time
railway logs --service mqtt --follow
```

### Environment Variables Management
```bash
# Set environment variable for specific service
railway variables set MQTT_HOST=your-broker-host --service mqtt

# View all variables for a service
railway variables --service mqtt
```

## Benefits of Multi-Service Architecture

1. **Isolation**: Each service runs independently
2. **Scaling**: Scale services based on individual needs
3. **Reliability**: Failure in one service doesn't affect others
4. **Resource Optimization**: Allocate resources per service requirements
5. **Monitoring**: Individual health checks and monitoring per service
6. **Deployment**: Deploy services independently

## Service Dependencies

```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│ Web Service │────│   Database   │────│   MQTT      │
│   (HTTP)    │    │   (Shared)   │    │  Broker     │
└─────────────┘    └──────────────┘    └─────────────┘
                           │                    │
                   ┌──────────────┐            │
                   │Worker Service│────────────┘
                   │  (Queues)    │
                   └──────────────┘
```

## Next Steps
1. Deploy each service following the steps above
2. Configure environment variables for each service
3. Test service communication and functionality
4. Set up monitoring and alerts
5. Configure custom domains (web service only)