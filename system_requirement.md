# System Requirements - Laravel IoT API with Location-Based Device Management

## Project Overview
A Laravel-based web application for managing ESP32 ultrasonic sensors with real-time data visualization, location-based device management, and Telegram notifications.

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **Excel Export**: Laravel Excel (Maatwebsite/Excel 3.1+)
- **Timezone**: Asia/Kuala_Lumpur (Malaysia/Singapore)

### Frontend
- **Templates**: Laravel Blade
- **UI Framework**: Bootstrap 5
- **Charts**: Chart.js
- **Data Tables**: DataTables.js
- **AJAX**: jQuery

### External Integration
- **Telegram Bot API**: For notifications
- **ESP32 API**: RESTful endpoints

## System Features

### 1. User Management ✅ COMPLETED
- User registration and login with Laravel Sanctum ✅
- **Role-based access control system** with Admin and User roles ✅
  - **Admin Role**: Full access to all features including user management ✅
  - **User Role**: Limited access to Dashboard and Devices only ✅
- Profile management with Telegram chat ID configuration ✅
- Password reset functionality ✅
- Session-based web authentication ✅
- User CRUD operations with role management ✅
- Navigation security (role-based menu visibility) ✅

### 2. Location-Based Device Management ✅ COMPLETED
- **Full Device CRUD Operations**: Create, read, update, and delete IoT devices ✅
- **Station Management**: Complete station information management with unique station IDs ✅
- **Location Structure**: ✅
  - State (Malaysian states dropdown with 16 states) ✅
  - District (Cascading dropdown based on selected state, 190+ districts) ✅
  - Address (Free text field for specific location) ✅
  - GPS Coordinates (Latitude/Longitude for Google Maps integration) ✅
- **Device Configuration**: ✅
  - MAC Address management ✅
  - Data collection intervals (customizable minutes) ✅
  - API token generation (secure 64-character tokens) ✅
- **Device Status Tracking**: Enhanced status system (online/offline/maintenance) ✅
- **Station Activity**: Soft deletion with station_active boolean flag ✅
- **Historical Data Management**: Complete sensor readings with Excel export ✅

### 3. Real-time IoT Monitoring Dashboard ✅ COMPLETED

#### Real-time Station Monitoring ✅ COMPLETED
- **Live Dashboard Interface**: Comprehensive real-time monitoring of all IoT stations ✅
- **Station Cards Display**: Individual cards for each station showing current status ✅
- **Real-time Data Updates**: Auto-refresh every 60 seconds with smart user interaction detection ✅
- **Summary Statistics**: Total, Online, Offline, and Maintenance station counts ✅
- **Current Sensor Readings**: Temperature, humidity, battery level, and signal strength ✅
- **Location Filtering**: Filter stations by State and District with cascading dropdowns ✅
- **Role-based Access Control**: Different access levels for Admin vs User roles ✅
- **Request Data Functionality**: Manual data request from ESP32 stations with database tracking ✅

#### Station Action Controls ✅ COMPLETED
- **Station Information Modal**: Detailed station information with edit capabilities ✅
- **Historical Data Charts**: Interactive Chart.js visualizations with dual-tab design ✅
  - **Environmental Data Tab**: Temperature and humidity trends over time ✅
  - **Technical Data Tab**: Battery voltage and signal strength monitoring ✅
- **Data Export Features**: Excel export functionality integrated within historical data modal ✅
- **Role-based Button Visibility**: Admin vs User access to different station functions ✅
- **Smart Dropdown Menus**: Bootstrap dropdowns with auto-refresh conflict resolution ✅

#### Device Management Tab ✅ COMPLETED
- **Complete Device Management Interface**: Full CRUD operations for IoT stations ✅
- **Station Information Modal**: Comprehensive station details with Google Maps integration ✅
- **Device Configuration Modal**: MAC address, data intervals, API token management ✅
- **Action Buttons**: Properly spaced buttons for all device operations ✅
- **Inline Success Messages**: Modal updates without popups for better UX ✅
- **Role-based Access**: Available to both Admin and User roles ✅
- **Modern Design**: Consistent with dashboard theme with Bootstrap 5 ✅

### 4. API Endpoints

#### User Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login with Sanctum token
- `POST /api/logout` - User logout
- `GET /api/user` - Get user profile
- `PUT /api/user` - Update user profile and Telegram settings

#### Device Management (Authenticated Users)
- `GET /api/devices` - List all devices with location
- `POST /api/devices` - Create device with location and API token
- `GET /api/devices/{id}` - Get device details including location
- `PUT /api/devices/{id}` - Update device and location
- `DELETE /api/devices/{id}` - Delete device
- `GET /api/devices/{id}/readings` - Get device sensor readings
- `GET /api/devices/{id}/chart-data?hours=24` - Get 24-hour chart data

#### Location Management
- `GET /api/states` - Get all Malaysian states
- `GET /api/states/{id}/districts` - Get districts for a state
- `GET /api/devices/by-location?state=X&district=Y` - Filter devices by location

#### ESP32 Device Endpoints (API Token Authentication) ✅ COMPLETED
- **`POST /api/config`** - Device configuration retrieval ✅
  - **Authentication**: API Token (Bearer or parameter)
  - **Response**: serverTime, updateRequest, nextCheckInterval, station_id, data_collection_time
  - **Timezone**: Asia/Singapore (KL time)
  - **Auto-reset**: Sets request_update to FALSE after config sent
  
- **`POST /api/upload`** - Sensor data upload with validation ✅
  - **Authentication**: API Token (Bearer or parameter) 
  - **Validation**: Temperature (-50 to 100°C), Humidity (0-100%), RSSI (-120 to 0 dBm), Battery (0-5V)
  - **Station Verification**: Validates station_code matches device
  - **Database Transaction**: Optimized for performance
  - **Auto-reset**: Sets request_update to FALSE after upload

**ESP32 Upload Request Format:**
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

**Server Response Format:**
```json
{
  "success": true,
  "message": "Sensor data uploaded successfully",
  "data": {
    "reading_id": 145,
    "device_id": 1,
    "station_id": "ST-NH9-1001",
    "timestamp": "2025-08-16 11:53:01",
    "request_update": false
  }
}
```


### 5. Database Schema with Location Support ✅ COMPLETED

```sql
-- Users table ✅ COMPLETED
users: 
  id, name, email, password, telegram_chat_id, role, created_at, updated_at

-- Malaysian location reference data ✅ COMPLETED
states: id, name, code (16 Malaysian states seeded) ✅
districts: id, state_id, name, district_code (190+ districts seeded) ✅

-- IoT Devices/Stations table ✅ COMPLETED
devices: 
  id, station_name, station_id, api_token, mac_address,
  data_interval_minutes, data_collection_time_minutes,
  state_id, district_id, address, gps_latitude, gps_longitude,
  status, station_active, request_update, last_seen, created_at, updated_at

-- Comprehensive sensor readings table ✅ COMPLETED
sensor_readings: 
  id, device_id, temperature, humidity, rssi, battery_voltage,
  reading_time, web_triggered, created_at, updated_at

**Key Features:**
- **API Token Authentication**: Secure 64-character tokens for ESP32 devices
- **Request Tracking**: request_update column for manual data request workflow
- **Location Integration**: State and district relationships for filtering
- **Timezone Support**: Asia/Singapore timezone for all timestamps
- **Performance Optimized**: Indexed for time-series queries and location filtering
```

### 6. Migration Files ✅ COMPLETED
- **Consolidated Device Migration**: Single migration file with all device fields ✅
- **Sensor Readings Migration**: Complete sensor data structure ✅
- **Location Reference Data**: States and districts with proper relationships ✅
- **Database Indexing**: Optimized indexes for performance ✅

### 7. Seeder Files ✅ COMPLETED
- **Device Seeder**: 5 realistic IoT devices with varied configurations ✅
- **Sensor Reading Seeder**: 8 hours of realistic sensor data (122 total readings) ✅
- **Location Seeders**: Complete Malaysian states and districts data ✅
- **User Seeder**: Admin and test users with proper roles ✅

### 8. Location-Enhanced Telegram Integration
- **Automated Notifications** when sensor data received
- **Enhanced Message Format**: 
  ```
  🔔 Device: {device_name}
  📍 Location: {state}, {district}
  📏 Distance: {distance}cm
  🕐 Time: {timestamp} (KL)
  ```
- **Location Context**: Include state and district in notifications
- **Per-device Configuration**: Individual Telegram chat IDs per device
- **Asia/Kuala_Lumpur Timezone**: All timestamps in Malaysian time

### 9. ESP32 Integration with Location
- **Bearer Token Authentication** for device API calls
- **Location-Aware Responses**: API responses include location context
- **JSON Format** matching existing ESP32 code structure
- **Enhanced Logging**: Location information in API logs
- **Support Features**:
  - Distance measurements with location context
  - Boot count tracking per device location
  - Web-triggered vs scheduled readings
  - Location-based device configuration

## Location-Specific Features

### 1. Malaysian States and Districts ✅ COMPLETED
- **Pre-seeded Data**: Complete Malaysian states and districts ✅
  - 16 Malaysian states with proper codes ✅
  - 190+ districts linked to respective states ✅
- **Database Seeders**: Automated data population ✅
- **Future Features**: Cascading dropdowns and validation ready ✅

### 2. Location-Based Analytics
- **Geographic Grouping**: Dashboard cards grouped by location
- **Location Statistics**: Device count per state/district
- **Regional Monitoring**: Filter historical data by region
- **Location Trends**: Analyze sensor patterns by location

### 3. Address Management
- **Flexible Address Field**: Free text for specific location details
- **Search Integration**: Search devices by address keywords
- **Location Display**: Complete location string in device cards
- **Export Support**: Full location details in CSV exports

## Technical Requirements

### Server Requirements
- **PHP**: 8.1+ with Laravel extensions
- **Database**: MySQL 8.0+ with timezone support
- **Web Server**: Apache/Nginx with URL rewriting
- **Memory**: Minimum 512MB RAM
- **Storage**: SSD recommended for time-series data

### Development Environment
- **Local Stack**: XAMPP/WAMP/Laragon with MySQL
- **Node.js**: For frontend asset compilation
- **Composer**: PHP dependency management
- **Git**: Version control system

### Security Features
- **Authentication**: Laravel Sanctum with CSRF protection
- **API Security**: Rate limiting for ESP32 endpoints
- **Token Management**: Secure device API token generation
- **Input Validation**: Location data validation and sanitization
- **SQL Injection Protection**: Laravel Eloquent ORM

### Performance Considerations
- **Database Indexing**: Optimized for location-based queries
- **Chart Data Optimization**: Efficient 24-hour time-series queries
- **Real-time Updates**: AJAX-based dashboard with minimal overhead
- **Location Caching**: Cache state/district data for performance
- **Pagination**: Efficient handling of large location-filtered datasets

## Location Data Management

### 1. Malaysian Geographic Data
- **States**: 13 states + 3 federal territories
- **Districts**: Complete district list per state
- **Data Seeding**: Laravel seeders for location data
- **Updates**: Maintainable location reference system

### 2. Location Validation
- **State-District Relationship**: Enforce valid combinations
- **Input Sanitization**: Clean location data input
- **Geocoding Support**: Optional coordinates for future features
- **Location Standards**: Consistent location naming

### 3. Migration Strategy
- **Database Migrations**: Version-controlled schema changes
- **Data Seeding**: Automated location data population
- **Backup Procedures**: Location data backup and restore
- **Update Procedures**: Handle location data changes

## Deployment Specifications
- **Production Configuration**: Environment-specific settings
- **SSL/HTTPS**: Secure connections for production
- **Database Optimization**: Production database tuning
- **Telegram Bot Setup**: Production bot configuration
- **Location Data Deployment**: Automated seeding in production
- **Monitoring**: Application and location query performance monitoring

## User Interface Requirements ✅ COMPLETED
- **Modern Theme**: Dark blue navigation with light content area ✅
- **Responsive Design**: Bootstrap 5 with mobile-friendly layout ✅
- **Role-based Navigation**: Admin/User specific menu items ✅
- **Clean Design**: Professional university project-appropriate interface ✅
- **Modern Components**: Gradient cards, hover effects, smooth animations ✅
- **Accessibility**: Proper form labels, ARIA attributes, semantic HTML ✅
- **Performance**: Optimized CSS, efficient layouts, fast loading ✅

## Implementation Status Summary

### ✅ **COMPLETED MODULES**
1. **User Management System**
   - Full CRUD operations for users ✅
   - Role-based access control (Admin/User) ✅
   - Authentication with Laravel Sanctum ✅
   - Profile management with Telegram integration ✅
   - Password management and security ✅

2. **Complete Database Foundation** 
   - User table with role system ✅
   - Malaysian states and districts data (16 states, 190+ districts) ✅
   - Complete device management schema ✅
   - Comprehensive sensor readings structure ✅
   - All migrations and seeders implemented ✅
   - Database relationships fully established ✅

3. **Full Device Management System**
   - Complete device CRUD operations ✅
   - Station information management ✅
   - Device configuration management ✅
   - Location integration (State/District cascading dropdowns) ✅
   - GPS coordinates and Google Maps integration ✅
   - API token generation for ESP32 devices ✅
   - Historical data management ✅

4. **Advanced Historical Data Features**
   - Interactive historical data modal ✅
   - Date range filtering with validation ✅
   - Real-time auto-population ✅
   - Excel export functionality with Laravel Excel package ✅
   - Comprehensive sensor data display ✅
   - Time-based data management ✅

5. **Modern UI/UX**
   - Dark blue navigation with gradient ✅
   - Light content area for readability ✅
   - Responsive dashboard with centered stats cards ✅
   - Role-based navigation visibility ✅
   - Bootstrap 5 with custom modern styling ✅
   - Inline success messages for better UX ✅
   - Properly spaced action buttons ✅

6. **Navigation & Security**
   - Role-based middleware protection ✅
   - Admin-only user management access ✅
   - Secure routing with authentication ✅
   - Clean navigation with role-appropriate links ✅

7. **Real-time Dashboard System**
   - Complete real-time IoT monitoring dashboard ✅
   - Live station status cards with auto-refresh ✅
   - Role-based access control for dashboard features ✅
   - State and District filtering with cascading dropdowns ✅
   - Manual data request functionality with database tracking ✅
   - Interactive historical data modals with Chart.js visualizations ✅
   - Smart auto-refresh with user interaction detection ✅
   - Bootstrap dropdown menus with conflict resolution ✅

8. **ESP32 API Integration System**
   - Complete ESP32 communication endpoints ✅
   - API Token authentication with Bearer and parameter support ✅
   - Device configuration endpoint with timezone support ✅
   - Sensor data upload with comprehensive validation ✅
   - Station code verification and security ✅
   - Database transaction optimization for performance ✅
   - Request/response cycle with auto-reset functionality ✅
   - Complete API documentation and testing guide ✅

### 🔄 **NEXT MODULES TO IMPLEMENT**
1. **Physical ESP32 Hardware Integration**
   - Deploy API endpoints to actual ESP32 devices
   - Test real-time data collection from sensors
   - Validate two-way communication in production
   
2. **Advanced Analytics Features**
   - Location-based analytics and reporting
   - Sensor data trends and patterns analysis
   - Alert system for abnormal readings
   
3. **Telegram Integration**
   - Automated notification system
   - Location-aware messages
   - Real-time alerts and monitoring

## Latest Updates (August 2025)

### ESP32 API Integration ✅ COMPLETED
- **Complete API endpoints**: `/api/config` and `/api/upload` fully implemented
- **Authentication system**: API Token with Bearer header and parameter support
- **Data validation**: Comprehensive validation for all sensor parameters
- **Security features**: Station code verification and device authentication
- **Performance optimization**: Database transactions and N+1 query fixes
- **Timezone support**: KL/Singapore time formatting for all timestamps
- **Request tracking**: Auto-reset request_update flag functionality
- **API documentation**: Complete Postman testing guide (API_TEST.md)
- **Error handling**: Comprehensive error responses for all scenarios
- **Dashboard integration**: Fixed "Last Seen" display and auto-refresh conflicts

### Real-time Dashboard Implementation ✅ COMPLETED
- **Complete dashboard replacement**: New real-time dashboard is now the default interface
- **Station monitoring cards**: Individual cards for each IoT station with live data display
- **Auto-refresh system**: 60-second refresh cycle with smart user interaction detection
- **Request data tracking**: Added `request_update` boolean column to devices table for tracking manual data requests
- **Location-based filtering**: State and District dropdown filters with cascading behavior
- **Role-based permissions**: Different dashboard access levels for Admin vs User roles
- **Interactive modals**: Station information and historical data modals with Chart.js visualizations
- **Excel export integration**: Historical data export functionality embedded in dashboard
- **Bootstrap dropdown fix**: Resolved dropdown menu conflicts with auto-refresh system
- **CSRF protection**: Fixed CSRF token issues for AJAX requests

## API Testing Documentation

### API_TEST.md ✅ COMPLETED
Comprehensive Postman testing guide including:
- **Complete endpoint documentation**: /api/config and /api/upload
- **Authentication methods**: Bearer token and parameter examples
- **Request/response formats**: JSON examples with all fields
- **Test cases**: 8 detailed test scenarios covering all use cases
- **Error handling**: All error responses documented
- **Troubleshooting guide**: Common issues and solutions
- **Sample data**: Real examples for immediate testing
- **Step-by-step instructions**: Easy to follow testing workflow

**Key Test Cases:**
1. Valid API Token configuration retrieval
2. Update request flow testing
3. Invalid token error handling
4. Valid sensor data upload
5. Station code mismatch testing
6. Validation error scenarios
7. Manual update request testing
8. Alternative authentication methods