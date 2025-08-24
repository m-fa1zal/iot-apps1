# ESP32 API Testing Guide - Postman

## Overview
This document provides instructions for testing the ESP32 config API endpoint using Postman.

## Base URL
```
https://localhost:8080/api
```

## Endpoints

### 1. ESP32 Configuration Endpoint

**Endpoint:** `POST /config`  
**Purpose:** ESP32 devices call this endpoint to get configuration and check for update requests

#### Authentication
The API uses **API Token Authentication** with two methods:

**Method 1: Bearer Token (Recommended)**
- Header: `Authorization`
- Value: `Bearer {api_token}`

**Method 2: Request Parameter**
- Body parameter: `api_token`
- Value: `{api_token}`

#### Request Headers
```
Content-Type: application/json
Authorization: Bearer {api_token}
```

#### Request Body
```json
{}
```
*Note: Empty body is acceptable. Authentication is handled via headers.*

#### Success Response (200 OK)
```json
{
  "serverTime": "2025-08-16 11:08:30",
  "updateRequest": false,
  "nextCheckInterval": 300,
  "station_id": "ST-NH9-1001",
  "data_collection_time": 1800
}
```

#### Response Fields
- `serverTime`: Current server date and time in format suitable for ESP32 processing (Asia/Singapore timezone) - e.g., "2025-08-16 11:08:30"
- `updateRequest`: Boolean indicating if dashboard user requested manual data update
- `nextCheckInterval`: Time in seconds between config checks (from device settings)
- `station_id`: Unique station identifier
- `data_collection_time`: Data collection duration in seconds

#### Error Responses

**Invalid API Token (401 Unauthorized)**
```json
{
  "error": "Invalid API token or device not found"
}
```

**Device Inactive (403 Forbidden)**
```json
{
  "error": "Device is not active"
}
```

**Server Error (500 Internal Server Error)**
```json
{
  "error": "Server error occurred"
}
```

## Postman Test Instructions

### Setup
1. Open Postman
2. Create a new request
3. Set method to `POST`
4. Set URL to: `https://localhost:8080/api/config`

### Test Case 1: Valid API Token (Bearer)

#### Headers
```
Content-Type: application/json
Authorization: Bearer [YOUR_ACTUAL_API_TOKEN_HERE]
```

#### Body
- Select `raw` and `JSON`
- Content: `{}`

#### Expected Result
- Status: `200 OK`
- Response with serverTime, updateRequest: false, nextCheckInterval, station_id, data_collection_time

### Test Case 2: Test Update Request Flow

#### Step 1: Check Current Status
- Use the same request as Test Case 1
- Note the `updateRequest` value (should be `false`)

#### Step 2: Trigger Update Request from Dashboard
- Open web dashboard: `https://localhost:8080/dashboard`
- Login with admin credentials
- Click "Request Data" button on any station card
- This sets `request_update = true` in database

#### Step 3: Check Config Again
- Repeat the API call from Test Case 1
- `updateRequest` should now be `true`

#### Step 4: Verify Auto-Reset
- Make the API call one more time
- `updateRequest` should be back to `false`
- This simulates ESP32 acknowledging the update request

### Test Case 3: Invalid API Token

#### Headers
```
Content-Type: application/json
Authorization: Bearer invalid_token_123
```

#### Body
```json
{}
```

#### Expected Result
- Status: `401 Unauthorized`
- Response: `{"error": "Invalid API token or device not found"}`

### Test Case 4: API Token via Parameter

#### Headers
```
Content-Type: application/json
```

#### Body
```json
{
  "api_token": "[YOUR_ACTUAL_API_TOKEN_HERE]"
}
```

#### Expected Result
- Status: `200 OK`
- Same response as Test Case 1

## Getting API Tokens

To get valid API tokens for testing, you can:

1. **Via Web Dashboard:**
   - Login to dashboard: `https://localhost:8080/dashboard`
   - Go to Devices section
   - View any device details
   - Copy the API token from device information

2. **Via Database (Development):**
   - Connect to your MySQL database
   - Query: `SELECT station_id, station_name, api_token FROM devices WHERE station_active = 1`

3. **Via Laravel Tinker (Development):**
   ```bash
   php artisan tinker
   App\Models\Device::select('station_id', 'api_token')->first()
   ```

## Important: Getting Real API Tokens

**⚠️ Note:** The API tokens are auto-generated unique 64-character strings. You must use actual tokens from your database.

**To get valid API tokens:**

1. **Via Web Dashboard (Recommended):**
   - Visit: `https://localhost:8080/dashboard` 
   - Login with: `admin@iot-apps.local` / `password123`
   - Go to "Devices" section
   - Click "View Details" on any device
   - Copy the API token from the device information modal

2. **Via Database Query:**
   ```sql
   SELECT station_id, station_name, api_token 
   FROM devices 
   WHERE station_active = 1 
   LIMIT 3;
   ```

**Sample Format (your tokens will be different):**
```
Station: ST-XXX-1001 (Weather Station Alpha)
Token: [64-character unique string from database]
```

## Testing Workflow

1. **Start Laravel Server:**
   ```bash
   php artisan serve --port=8080
   ```

2. **Test Basic Config Retrieval:**
   - Use Test Case 1 to verify API works
   - Check response format matches expected structure

3. **Test Update Request Flow:**
   - Follow Test Case 2 to verify update request mechanism
   - Confirm auto-reset functionality works

4. **Test Error Handling:**
   - Use Test Case 3 to verify invalid token handling
   - Test with inactive devices if available

5. **Test Alternative Authentication:**
   - Use Test Case 4 to verify parameter-based auth works

## Upload Endpoint Test Cases

### Test Case 5: Valid Sensor Data Upload

#### Setup
1. Set method to `POST`
2. Set URL to: `https://localhost:8080/api/upload`

#### Headers
```
Content-Type: application/json
Authorization: Bearer [YOUR_ACTUAL_API_TOKEN_HERE]
```

#### Body
```json
{
  "station_code": "ST-NH9-1001",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": false
}
```

#### Expected Result
- Status: `200 OK`
- Response with success: true, reading_id, device_id, station_id, timestamp

### Test Case 6: Station Code Mismatch

#### Headers
```
Content-Type: application/json
Authorization: Bearer [YOUR_ACTUAL_API_TOKEN_HERE]
```

#### Body
```json
{
  "station_code": "WRONG-CODE-123",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85
}
```

#### Expected Result
- Status: `403 Forbidden`
- Response: `{"success": false, "error": "Station code does not match device"}`

### Test Case 7: Validation Errors

#### Headers
```
Content-Type: application/json
Authorization: Bearer [YOUR_ACTUAL_API_TOKEN_HERE]
```

#### Body (Invalid data)
```json
{
  "station_code": "ST-NH9-1001",
  "humidity": 150,
  "temperature": "invalid",
  "rssi": 50,
  "battery_voltage": -1
}
```

#### Expected Result
- Status: `422 Unprocessable Entity`
- Response with validation errors for each invalid field

### Test Case 8: Manual Update Request

#### Headers
```
Content-Type: application/json
Authorization: Bearer [YOUR_ACTUAL_API_TOKEN_HERE]
```

#### Body
```json
{
  "station_code": "ST-NH9-1001",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": true
}
```

#### Expected Result
- Status: `200 OK`
- Data saved with web_triggered = true in database

## Notes

- All timestamps are in Asia/Singapore timezone (same as Kuala Lumpur)
- `serverTime` format: "YYYY-MM-DD HH:MM:SS" (24-hour format) for easy ESP32 parsing
- The `nextCheckInterval` is based on device's `data_interval_minutes` setting
- The `data_collection_time` is converted from minutes to seconds
- Each successful API call updates the device's `last_seen` timestamp and sets status to 'online'
- The `updateRequest` flag is automatically reset to `false` after being sent to ESP32

## Troubleshooting

**Common Issues:**

1. **Connection Refused:**
   - Ensure Laravel server is running (`php artisan serve --port=8080`)
   - Check if port 8080 is available

2. **401 Unauthorized:**
   - Verify API token is correct and active
   - Check device is not marked as inactive in database

3. **500 Internal Server Error:**
   - Check Laravel logs in `storage/logs/laravel.log`
   - Verify database connection is working

4. **Route Not Found:**
   - Ensure you're using POST method
   - Verify URL is correct: `/api/config` not `/config`

### 2. ESP32 Data Upload Endpoint

**Endpoint:** `POST /upload`  
**Purpose:** ESP32 devices send sensor data to the server for storage

#### Authentication
Same as config endpoint - uses **API Token Authentication**:
- Header: `Authorization: Bearer {api_token}` (Recommended)
- Body parameter: `api_token`

#### Request Headers
```
Content-Type: application/json
Authorization: Bearer {api_token}
```

#### Request Body
```json
{
  "station_code": "ST-NH9-1001",
  "humidity": 65.5,
  "temperature": 28.3,
  "rssi": -67,
  "battery_voltage": 3.85,
  "update_request": false
}
```

#### Request Fields
- `station_code`: Station ID that must match device's station_id (required)
- `humidity`: Humidity percentage 0-100 (required)
- `temperature`: Temperature in Celsius -50 to 100 (required) 
- `rssi`: Signal strength in dBm -120 to 0 (required)
- `battery_voltage`: Battery voltage 0-5V (required)
- `update_request`: Boolean indicating if this was a manual update request (optional)

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Sensor data uploaded successfully",
  "data": {
    "reading_id": 45,
    "device_id": 1,
    "station_id": "ST-NH9-1001",
    "timestamp": "2025-08-16 11:46:10",
    "request_update": false
  }
}
```

#### Error Responses

**Invalid API Token (401 Unauthorized)**
```json
{
  "success": false,
  "error": "Invalid API token or device not found"
}
```

**Device Inactive (403 Forbidden)**
```json
{
  "success": false,
  "error": "Device is not active"
}
```

**Station Code Mismatch (403 Forbidden)**
```json
{
  "success": false,
  "error": "Station code does not match device"
}
```

**Validation Error (422 Unprocessable Entity)**
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "humidity": ["The humidity must be between 0 and 100."],
    "temperature": ["The temperature field is required."]
  }
}
```

**Server Error (500 Internal Server Error)**
```json
{
  "success": false,
  "error": "Server error occurred"
}
```

## Common Issues & Solutions

### Issue: "Invalid API token or device not found"

This error occurs when:
1. **Wrong API Token**: The token doesn't exist in database
2. **Inactive Device**: Device exists but `station_active = false`
3. **Wrong Method**: Using GET instead of POST

**Solutions:**
1. **Get Real Token**: Follow the "Getting API Tokens" section above
2. **Use POST Method**: Ensure Postman method is set to POST
3. **Check Token Format**: API tokens are exactly 64 characters long
4. **Use Body Instead of URL**: For POST requests, put `api_token` in request body:
   ```json
   {
     "api_token": "your_real_token_here"
   }
   ```
   Instead of URL parameter: `?api_token=your_token`

### Issue: Wrong Port (8080 vs 8000)
- Laravel typically runs on port 8000 by default
- Start server with: `php artisan serve --port=8080` to match your URL
- Or change your URL to use port 8000