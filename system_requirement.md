# System Requirements - Laravel IoT API with Location-Based Device Management

## Project Overview
A Laravel-based web application for managing ESP32 ultrasonic sensors with real-time data visualization, location-based device management, and Telegram notifications.

## Technology Stack

### Backend
- **Framework**: Laravel (latest version)
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
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

### 1. User Management
- User registration and login with Laravel Sanctum
- All authenticated users have admin privileges (no role system)
- Profile management with Telegram chat ID configuration
- Password reset functionality
- Session-based web authentication

### 2. Location-Based Device Management
- Add/edit/delete ESP32 devices with complete location details
- Generate unique API tokens for each device
- **Location Structure**:
  - State (Malaysian states dropdown)
  - District (Cascading dropdown based on selected state)
  - Address (Free text field for specific location)
- Device status tracking (online/offline)
- Last seen timestamp for each device
- Location-based device filtering and grouping

### 3. Web Dashboard with Location Features

#### Main Dashboard
- **Device Cards Layout**: Grid display with location-based grouping
- **Location Information**: Each card shows State, District, and Address
- **Device Status**: Real-time online/offline indicators
- **Latest Readings**: Distance measurements with KL timezone timestamps
- **24-Hour Distance Charts**: Individual Chart.js line charts per device
- **Location Filtering**: Dropdown filters for State and District
- **Auto-refresh**: Dashboard updates every 30 seconds via AJAX

#### Historical Data Tab
- **Comprehensive Data Table** with columns:
  - Device Name, State, District, Address
  - Distance readings, Boot Count
  - Timestamp (Asia/Kuala_Lumpur timezone)
  - Web-triggered indicator
- **Advanced Filtering**:
  - Date range picker
  - State/District cascading dropdowns
  - Device name search
- **Export Functionality**: CSV export with location data
- **Pagination**: Efficient handling of large datasets

#### Device Management Tab
- **Device CRUD Operations** with location fields
- **Location Configuration**:
  - State dropdown (pre-populated Malaysian states)
  - District dropdown (cascading based on state selection)
  - Address field for specific location details
- **API Token Management**: Generate and regenerate device tokens
- **Telegram Configuration**: Set chat ID per device

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

#### ESP32 Device Endpoints (API Token Authentication)
- `POST /api/config` - Device configuration retrieval
- `POST /api/upload` - Sensor data upload with location context

### 5. Database Schema with Location Support

```sql
-- Users table
users: 
  id, name, email, password, telegram_chat_id, created_at, updated_at

-- Devices table with location fields
devices: 
  id, user_id, name, device_id, api_token, 
  state, district, address, status, last_seen,
  created_at, updated_at

-- Sensor readings table
sensor_readings: 
  id, device_id, distance, boot_count, web_triggered, created_at

-- Malaysian location reference data
states: id, name, code
districts: id, state_id, name
```

### 6. Location-Enhanced Telegram Integration
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

### 7. ESP32 Integration with Location
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

### 1. Malaysian States and Districts
- **Pre-seeded Data**: Complete Malaysian states and districts
- **Cascading Dropdowns**: State selection filters district options
- **Validation**: Ensure valid state-district combinations
- **Search Functionality**: Quick location lookup in device lists

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

## User Interface Requirements
- **Responsive Design**: Mobile-friendly location selection
- **Intuitive Navigation**: Easy location-based filtering
- **Real-time Visualization**: Location-aware dashboard updates
- **Clean Design**: University project-appropriate interface
- **Malaysian Context**: Location-specific UI elements
- **Accessibility**: Location dropdowns with proper labeling
- **Performance**: Fast location filtering and chart updates