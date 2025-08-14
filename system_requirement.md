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

### 3. Web Dashboard with Location Features ✅ PARTIALLY COMPLETED

#### Main Dashboard ✅ COMPLETED
- **Modern Dark Navigation**: Dark blue gradient navigation bar ✅
- **Light Content Area**: Clean, professional light theme for main content ✅
- **Statistics Cards**: Centered 3-card layout showing device metrics ✅
  - Total Devices with purple gradient icon ✅
  - Online Devices with green gradient icon ✅
  - Offline Devices with orange gradient icon ✅
- **Responsive Design**: Balanced layout across all screen sizes ✅
- **Role-based Access**: Different dashboard views for Admin vs User ✅
- **Modern UI Components**: Cards with hover effects and gradients ✅

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

#### Device Management Tab ✅ COMPLETED
- **Basic Device Interface**: Placeholder device page for users ✅
- **Role-based Access**: Available to both Admin and User roles ✅
- **Modern Design**: Consistent with dashboard theme ✅
- **Device Statistics**: Empty state with metrics placeholder ✅
- **Future Ready**: Structure prepared for full device CRUD implementation ✅

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
-- Users table ✅ COMPLETED
users: 
  id, name, email, password, telegram_chat_id, role, created_at, updated_at

-- Malaysian location reference data ✅ COMPLETED
states: id, name, code (16 Malaysian states seeded) ✅
districts: id, state_id, name (190+ districts seeded) ✅

-- Future tables for device management
devices: 
  id, user_id, name, device_id, api_token, 
  state, district, address, status, last_seen,
  created_at, updated_at

-- Sensor readings table
sensor_readings: 
  id, device_id, distance, boot_count, web_triggered, created_at
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
   - Full CRUD operations for users
   - Role-based access control (Admin/User)
   - Authentication with Laravel Sanctum
   - Profile management with Telegram integration
   - Password management and security

2. **Database Foundation** 
   - User table with role system
   - Malaysian states and districts data (16 states, 190+ districts)
   - Proper migrations and seeders
   - Database relationships ready

3. **Modern UI/UX**
   - Dark blue navigation with gradient
   - Light content area for readability
   - Responsive dashboard with centered stats cards
   - Role-based navigation visibility
   - Bootstrap 5 with custom modern styling

4. **Navigation & Security**
   - Role-based middleware protection
   - Admin-only user management access
   - Secure routing with authentication
   - Clean navigation with role-appropriate links

### 🔄 **NEXT MODULES TO IMPLEMENT**
1. **Device Management System**
   - Device CRUD operations
   - Location integration (State/District selection)
   - API token generation for ESP32 devices
   
2. **Real-time Dashboard**
   - Device status monitoring
   - Sensor data visualization
   - Location-based filtering
   
3. **API Endpoints**
   - ESP32 device communication
   - Sensor data collection
   - Device configuration management
   
4. **Telegram Integration**
   - Notification system
   - Location-aware messages
   - Real-time alerts