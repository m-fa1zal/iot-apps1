# Database Relationship Documentation - IoT Sensor Management System

## Entity Relationship Diagram (ERD)

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     USERS       │       │    DEVICES      │       │ SENSOR_READINGS │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │◄──────┤ id (PK)         │
│ name            │       │ station_name    │   1:M │ device_id (FK)  │
│ email           │       │ station_id      │       │ temperature     │
│ password        │       │ api_token       │       │ humidity        │
│ telegram_chat_id│       │ mac_address     │       │ rssi            │
│ role            │       │ data_interval   │       │ battery_voltage │
│ created_at      │       │ data_collection │       │ reading_time    │
│ updated_at      │       │ state_id (FK)   │◄──────┤ web_triggered   │
└─────────────────┘       │ district_id (FK)│   M:1 │ created_at      │
                          │ address         │       │ updated_at      │
                          │ gps_latitude    │       └─────────────────┘
                          │ gps_longitude   │
                          │ status          │
                          │ station_active  │
                          │ request_update  │
                          │ last_seen       │
                          │ created_at      │
                          │ updated_at      │
                          └─────────────────┘
                                   │
                          ┌────────┴────────┐
                          │                 │
               ┌─────────────────┐ ┌─────────────────┐
               │     STATES      │ │   DISTRICTS     │
               ├─────────────────┤ ├─────────────────┤
               │ id (PK)         │ │ id (PK)         │
               │ name            │ │ state_id (FK)   │◄──┐
               │ code            │ │ name            │   │
               └─────────────────┘ │ district_code   │   │
                         ▲         └─────────────────┘   │
                         └─────────────────────────────────┘
```

## Table Specifications

### 1. USERS Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telegram_chat_id VARCHAR(255) NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Purpose**: Store user authentication and profile information with role-based access
**Indexes**: 
- PRIMARY KEY (id)
- UNIQUE INDEX (email)
- INDEX (role) for role-based queries

### 2. DEVICES Table
```sql
CREATE TABLE devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_name VARCHAR(255) NOT NULL,
    station_id VARCHAR(255) UNIQUE NOT NULL,
    api_token VARCHAR(64) UNIQUE NOT NULL,
    mac_address VARCHAR(255) NULL,
    data_interval_minutes INTEGER DEFAULT 2,
    data_collection_time_minutes INTEGER DEFAULT 30,
    state_id BIGINT UNSIGNED NOT NULL,
    district_id BIGINT UNSIGNED NOT NULL,
    address TEXT NULL,
    gps_latitude DECIMAL(10,8) NULL,
    gps_longitude DECIMAL(11,8) NULL,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
    station_active BOOLEAN DEFAULT TRUE,
    request_update BOOLEAN DEFAULT FALSE,
    last_seen TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE
);
```

**Purpose**: Store IoT station information with comprehensive location and configuration details. Includes request_update flag to track remote data requests.
**Relationships**: 
- Many-to-One with STATES (state_id → states.id)
- Many-to-One with DISTRICTS (district_id → districts.id)
- One-to-Many with SENSOR_READINGS

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (state_id, district_id)
- UNIQUE INDEX (station_id)
- UNIQUE INDEX (api_token)
- INDEX (state_id, district_id) for location filtering
- INDEX (last_seen) for status queries
- INDEX (status) for filtering

### 3. SENSOR_READINGS Table
```sql
CREATE TABLE sensor_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    temperature DECIMAL(5,2) NULL COMMENT 'Temperature in Celsius',
    humidity DECIMAL(5,2) NULL COMMENT 'Humidity percentage',
    rssi INTEGER NULL COMMENT 'Signal strength in dBm',
    battery_voltage DECIMAL(4,2) NULL COMMENT 'Battery voltage',
    reading_time TIMESTAMP NULL COMMENT 'Time when reading was taken',
    web_triggered BOOLEAN DEFAULT FALSE COMMENT 'Manual vs scheduled reading',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);
```

**Purpose**: Store comprehensive sensor measurement data from IoT devices
**Relationships**: 
- Many-to-One with DEVICES (device_id → devices.id)

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (device_id)
- INDEX (device_id, reading_time) for time-series queries
- INDEX (reading_time) for historical data queries

### 4. STATES Table (Reference Data)
```sql
CREATE TABLE states (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Purpose**: Malaysian states reference data (16 states seeded)
**Relationships**: 
- One-to-Many with DISTRICTS
- One-to-Many with DEVICES

### 5. DISTRICTS Table (Reference Data)
```sql
CREATE TABLE districts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    district_code VARCHAR(10) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
);
```

**Purpose**: Malaysian districts reference data (190+ districts seeded)
**Relationships**: 
- Many-to-One with STATES
- One-to-Many with DEVICES

## Relationship Details

### 1. States → Districts (One-to-Many)
- **Relationship**: One state contains multiple districts
- **Foreign Key**: districts.state_id → states.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Used for location cascading dropdowns and validation

### 2. States → Devices (One-to-Many)
- **Relationship**: One state can have multiple IoT devices/stations
- **Foreign Key**: devices.state_id → states.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Devices must belong to valid Malaysian states

### 3. Districts → Devices (One-to-Many)
- **Relationship**: One district can have multiple IoT devices/stations
- **Foreign Key**: devices.district_id → districts.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Devices must belong to valid districts within selected state

### 4. Devices → Sensor_Readings (One-to-Many)
- **Relationship**: One device generates multiple sensor readings over time
- **Foreign Key**: sensor_readings.device_id → devices.id
- **Cascade**: ON DELETE CASCADE (delete readings when device deleted)
- **Business Rule**: Readings are time-series data for historical analysis and Excel export

## Query Patterns

### 1. Dashboard Queries
```sql
-- Get devices with latest readings and location
SELECT d.*, sr.temperature, sr.humidity, sr.reading_time as last_reading,
       s.name as state_name, dist.name as district_name
FROM devices d
LEFT JOIN sensor_readings sr ON d.id = sr.device_id
LEFT JOIN states s ON d.state_id = s.id
LEFT JOIN districts dist ON d.district_id = dist.id
WHERE sr.id = (
    SELECT MAX(id) FROM sensor_readings WHERE device_id = d.id
) AND d.station_active = TRUE;

-- Get historical data for Excel export
SELECT sr.temperature, sr.humidity, sr.rssi, sr.battery_voltage, 
       sr.reading_time, sr.web_triggered, sr.created_at,
       d.station_name, d.station_id
FROM sensor_readings sr
JOIN devices d ON sr.device_id = d.id
WHERE sr.device_id = ? AND DATE(sr.reading_time) BETWEEN ? AND ?
ORDER BY sr.reading_time DESC;
```

### 2. Historical Data Queries
```sql
-- Get readings with device and location info for modal display
SELECT sr.*, d.station_name, d.station_id, s.name as state_name, 
       dist.name as district_name
FROM sensor_readings sr
JOIN devices d ON sr.device_id = d.id
JOIN states s ON d.state_id = s.id
JOIN districts dist ON d.district_id = dist.id
WHERE sr.reading_time BETWEEN ? AND ?
ORDER BY sr.reading_time DESC;

-- Get sensor data with date filtering
SELECT * FROM sensor_readings 
WHERE device_id = ? AND DATE(reading_time) = CURDATE()
ORDER BY reading_time DESC;
```

### 3. Location Queries
```sql
-- Get devices by location with full details
SELECT d.*, s.name as state_name, dist.name as district_name
FROM devices d
JOIN states s ON d.state_id = s.id
JOIN districts dist ON d.district_id = dist.id
WHERE d.state_id = ? AND d.district_id = ? AND d.station_active = TRUE
ORDER BY d.station_name;

-- Get cascading location data for dropdowns
SELECT s.id, s.name as state_name, s.code
FROM states s
ORDER BY s.name;

SELECT d.id, d.name as district_name, d.district_code
FROM districts d
WHERE d.state_id = ?
ORDER BY d.name;
```

## Data Integrity Constraints

### 1. Referential Integrity
- All foreign key relationships enforced
- CASCADE deletes for dependent data
- NULL constraints on required fields
- Station active boolean flag for soft deletion
- Request update boolean flag for tracking remote data requests

### 2. Business Rules
- Station IDs must be unique across system
- API tokens must be unique and secure (64 character tokens)
- State/District combination must be valid
- Sensor readings must have valid device reference
- GPS coordinates validation (latitude: -90 to 90, longitude: -180 to 180)

### 3. Data Validation
- Temperature stored as DECIMAL(5,2) for precision (-99.99 to 999.99°C)
- Humidity stored as DECIMAL(5,2) for percentage (0.00 to 100.00%)
- RSSI stored as INTEGER for signal strength (dBm values)
- Battery voltage as DECIMAL(4,2) for precision (0.00 to 99.99V)
- Timestamps in Asia/Kuala_Lumpur timezone
- Boolean flags for web_triggered readings vs scheduled
- Boolean flags for request_update to track pending data requests
- ENUM constraints for device status (online/offline/maintenance)

## Performance Considerations

### 1. Indexing Strategy
- Primary keys on all tables
- Foreign key indexes for joins
- Composite index on (device_id, created_at) for time-series
- Unique indexes on business keys (email, device_id, api_token)

### 2. Query Optimization
- Time-series partitioning consideration for large datasets
- Efficient pagination for historical data
- Optimized dashboard queries with proper indexing

### 3. Data Archival
- Consider archival strategy for old sensor readings
- Backup and recovery procedures
- Data retention policies for compliance