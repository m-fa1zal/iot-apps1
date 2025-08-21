# MQTT Log Size Optimization Report

## Problem Identified
The `laravel.log` file has grown to **12.7 GB** due to excessive logging from MQTT operations.

## Root Causes

### 1. **Excessive Info Logging in MQTT Listener**
- Every MQTT message was logged with full payload
- Every response was logged with complete JSON
- Heartbeat messages (frequent) were generating multiple log entries
- Data upload messages were logged individually

### 2. **Verbose MQTT Service Logging**
- Every connection, subscription, and publish operation was logged
- All device authentications were logged
- Configuration updates were logged at INFO level

### 3. **No Log Rotation**
- All logs were accumulating in a single `laravel.log` file
- No automatic cleanup or rotation was implemented

## Solutions Implemented

### 1. **Reduced Logging Verbosity**

#### Before:
```php
Log::info("MQTT message received", ['topic' => $topic, 'payload' => $payload]);
Log::info("Heartbeat processed for device: {$stationId}");
Log::info("Data upload processed for device: {$stationId}");
```

#### After:
```php
Log::debug("MQTT message received", ['topic' => $topic, 'payload_length' => strlen($payload)]);
Log::debug("Heartbeat processed for device: {$stationId}");
Log::debug("Data upload processed for device: {$stationId}");
```

### 2. **Optimized Console Output**
- Removed redundant console info messages
- Condensed multi-line outputs to single lines
- Only show errors and warnings in console

### 3. **Added Log Rotation**
- Added dedicated MQTT log channel with daily rotation
- Configured to keep only 7 days of logs
- Set log level to WARNING (only errors and warnings)

### 4. **Log Cleanup Script**
Created `cleanup-logs.bat` to:
- Backup current large log file
- Clear the existing log file
- Show before/after file sizes

## Configuration Changes

### Logging Configuration (`config/logging.php`)
```php
'mqtt' => [
    'driver' => 'daily',
    'path' => storage_path('logs/mqtt.log'),
    'level' => env('LOG_LEVEL', 'warning'), // Only warnings and errors
    'days' => env('LOG_DAILY_DAYS', 7), // Keep 7 days only
    'replace_placeholders' => true,
],
```

### Environment Variables (Add to `.env`)
```env
# Use daily log rotation instead of single file
LOG_CHANNEL=daily

# Keep logs for 7 days only
LOG_DAILY_DAYS=7

# Set log level to warning to reduce verbosity
LOG_LEVEL=warning
```

## Expected Results

### Log Size Reduction:
- **Before:** 12.7 GB single file
- **After:** Daily files ~10-50 MB each, automatically cleaned up

### Performance Improvement:
- Faster log writes (smaller files)
- Reduced disk I/O
- Better application performance

### Maintenance:
- Automatic log rotation and cleanup
- Easier to review recent logs
- Reduced storage requirements

## Monitoring

### Files to Monitor:
- `storage/logs/laravel-YYYY-MM-DD.log` (daily files)
- `storage/logs/mqtt-YYYY-MM-DD.log` (MQTT specific logs)

### Log Levels Used:
- **ERROR:** Critical failures requiring attention
- **WARNING:** Issues that should be monitored
- **DEBUG:** Detailed information (disabled in production)

## Usage Instructions

1. **Clean existing logs:**
   ```bash
   cleanup-logs.bat
   ```

2. **Update environment:**
   ```env
   LOG_CHANNEL=daily
   LOG_LEVEL=warning
   LOG_DAILY_DAYS=7
   ```

3. **Restart MQTT listener:**
   ```bash
   php artisan mqtt:listen
   ```

4. **Monitor log sizes:**
   ```bash
   dir storage\logs\*.log
   ```

## Maintenance Schedule

- **Daily:** Automatic log rotation (no action needed)
- **Weekly:** Check log directory size
- **Monthly:** Review any WARNING/ERROR patterns

This optimization should reduce log file sizes by **99%** while maintaining essential error tracking and debugging capabilities.