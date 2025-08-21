# Database Relationship Documentation - IoT Sensor Management System

## Entity Relationship Diagram (ERD)

```
┌─────────────────┐    ┌────────────────────────┐    ┌─────────────────┐
│     USERS       │    │ STATION_INFORMATION    │    │ SENSOR_READINGS │
├─────────────────┤    ├────────────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)                │◄───┤ id (PK)         │
│ name            │    │ station_name           │1:M │ station_id (FK) │
│ email           │    │ station_id (UNIQUE)    │    │ temperature     │
│ password        │    │ state_id (FK)          │    │ humidity        │
│ telegram_chat_id│    │ district_id (FK)       │    │ rssi            │
│ role            │    │ address                │    │ battery_voltage │
│ last_login_at   │    │ gps_latitude           │    │ reading_time    │
│ created_at      │    │ gps_longitude          │    │ web_triggered   │
│ updated_at      │    │ station_active         │    │ created_at      │
└─────────────────┘    │ created_at             │    │ updated_at      │
                       │ updated_at             │    └─────────────────┘
                       └────────────────────────┘
                                    │                 
                       ┌────────────┼────────────────┐
                       │            │                │
              ┌─────────────────┐   │   ┌─────────────────────┐
              │ DEVICE_CONFIG   │   │   │   DEVICE_STATUS     │
              ├─────────────────┤   │   ├─────────────────────┤
              │ id (PK)         │   │   │ station_id (PK,FK)  │
              │ station_id (FK) │◄──┘   │ status              │
              │ api_token       │       │ request_update      │
              │ mac_address     │       │ last_seen           │
              │ data_interval   │       │ created_at          │
              │ data_collection │       │ updated_at          │
              │ config_update   │       └─────────────────────┘
              │ created_at      │                  
              │ updated_at      │       ┌─────────────────────┐
              └─────────────────┘       │   MQTT_TASK_LOGS    │
                       │                ├─────────────────────┤
                       └────────────────┤ id (PK)             │
                                        │ station_id (FK)     │
                                        │ topic               │
                                        │ task_type           │
                                        │ direction           │
                                        │ status              │
                                        │ received_at         │
                                        └─────────────────────┘
                                                 │
               ┌─────────────────┐  ┌───────────────────────────┐
               │     STATES      │  │       DISTRICTS           │
               ├─────────────────┤  ├───────────────────────────┤
               │ id (PK)         │  │ id (PK)                   │
               │ name            │  │ state_id (FK)             │◄──┐
               │ code            │  │ name                      │   │
               │ created_at      │  │ district_code             │   │
               │ updated_at      │  │ created_at                │   │
               └─────────────────┘  │ updated_at                │   │
                         ▲          └───────────────────────────┘   │
                         └──────────────────────────────────────────┘
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
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Purpose**: Store user authentication and profile information with role-based access
**Indexes**: 
- PRIMARY KEY (id)
- UNIQUE INDEX (email)
- INDEX (role) for role-based queries
- INDEX (last_login_at) for activity tracking

### 2. STATION_INFORMATION Table
```sql
CREATE TABLE station_information (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_name VARCHAR(255) NOT NULL,
    station_id VARCHAR(255) UNIQUE NOT NULL,
    state_id BIGINT UNSIGNED NOT NULL,
    district_id BIGINT UNSIGNED NOT NULL,
    address TEXT NULL,
    gps_latitude DECIMAL(10,8) NULL,
    gps_longitude DECIMAL(11,8) NULL,
    station_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE
);
```

**Purpose**: Store IoT station basic information with location and identification details.
**Relationships**: 
- Many-to-One with STATES (state_id → states.id)
- Many-to-One with DISTRICTS (district_id → districts.id)
- One-to-One with DEVICE_CONFIGURATIONS
- One-to-One with DEVICE_STATUS
- One-to-Many with SENSOR_READINGS
- One-to-Many with MQTT_TASK_LOGS

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE INDEX (station_id)
- INDEX (state_id, district_id) for location filtering
- INDEX (station_id) for quick lookups

### 3. DEVICE_CONFIGURATIONS Table
```sql
CREATE TABLE device_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(255) NOT NULL,
    api_token VARCHAR(64) UNIQUE NOT NULL,
    mac_address VARCHAR(255) NULL,
    data_interval INTEGER DEFAULT 2,
    data_collection_time INTEGER DEFAULT 30,
    configuration_update BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (station_id) REFERENCES station_information(station_id) ON DELETE CASCADE
);
```

**Purpose**: Store device-specific configuration parameters and settings.
**Relationships**: 
- One-to-One with STATION_INFORMATION (station_id → station_information.station_id)

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE INDEX (api_token)
- INDEX (station_id)
- FOREIGN KEY (station_id)

### 4. DEVICE_STATUS Table
```sql
CREATE TABLE device_status (
    station_id VARCHAR(255) PRIMARY KEY,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
    request_update BOOLEAN DEFAULT FALSE,
    last_seen TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (station_id) REFERENCES station_information(station_id) ON DELETE CASCADE
);
```

**Purpose**: Track real-time device status and communication flags.
**Relationships**: 
- One-to-One with STATION_INFORMATION (station_id → station_information.station_id)

**Indexes**:
- PRIMARY KEY (station_id)
- INDEX (status) for filtering
- INDEX (last_seen) for status queries

### 5. SENSOR_READINGS Table
```sql
CREATE TABLE sensor_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(255) NOT NULL,
    temperature DECIMAL(5,2) NULL COMMENT 'Temperature in Celsius from DHT11',
    humidity DECIMAL(5,2) NULL COMMENT 'Humidity percentage from DHT11',
    rssi INTEGER NULL COMMENT 'RSSI value for WiFi signal strength in dBm',
    battery_voltage DECIMAL(4,2) NULL COMMENT 'Battery voltage in volts',
    reading_time TIMESTAMP COMMENT 'Time when ESP32 captured the reading',
    web_triggered BOOLEAN DEFAULT FALSE COMMENT 'Manual vs scheduled reading',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (station_id) REFERENCES station_information(station_id) ON DELETE CASCADE
);
```

**Purpose**: Store comprehensive sensor measurement data from IoT devices
**Relationships**: 
- Many-to-One with STATION_INFORMATION (station_id → station_information.station_id)

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (station_id)
- INDEX (station_id, created_at) for time-series queries
- INDEX (station_id, reading_time) for historical data queries

### 6. MQTT_TASK_LOGS Table
```sql
CREATE TABLE mqtt_task_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(255) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    task_type ENUM('heartbeat', 'configuration_update', 'data_upload') NOT NULL,
    direction ENUM('request', 'response') NOT NULL,
    status ENUM('pending', 'sent', 'received', 'acknowledged', 'failed', 'timeout') NOT NULL,
    received_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (station_id) REFERENCES station_information(station_id) ON DELETE CASCADE
);
```

**Purpose**: Track MQTT communication logs for debugging and monitoring
**Relationships**: 
- Many-to-One with STATION_INFORMATION (station_id → station_information.station_id)

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (station_id)
- INDEX (station_id, task_type) for filtering by device and task
- INDEX (status, received_at) for performance monitoring
- INDEX (topic) for topic-based queries

### 7. STATES Table (Reference Data)
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
- One-to-Many with STATION_INFORMATION

### 8. DISTRICTS Table (Reference Data)
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
- One-to-Many with STATION_INFORMATION

## Relationship Details

### 1. States → Districts (One-to-Many)
- **Relationship**: One state contains multiple districts
- **Foreign Key**: districts.state_id → states.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Used for location cascading dropdowns and validation

### 2. States → Station_Information (One-to-Many)
- **Relationship**: One state can have multiple IoT stations
- **Foreign Key**: station_information.state_id → states.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Stations must belong to valid Malaysian states

### 3. Districts → Station_Information (One-to-Many)
- **Relationship**: One district can have multiple IoT stations
- **Foreign Key**: station_information.district_id → districts.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Stations must belong to valid districts within selected state

### 4. Station_Information → Device_Configurations (One-to-One)
- **Relationship**: Each station has exactly one configuration record
- **Foreign Key**: device_configurations.station_id → station_information.station_id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Configuration settings are station-specific

### 5. Station_Information → Device_Status (One-to-One)
- **Relationship**: Each station has exactly one status record
- **Foreign Key**: device_status.station_id → station_information.station_id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Status tracking is station-specific

### 6. Station_Information → Sensor_Readings (One-to-Many)
- **Relationship**: One station generates multiple sensor readings over time
- **Foreign Key**: sensor_readings.station_id → station_information.station_id
- **Cascade**: ON DELETE CASCADE (delete readings when station deleted)
- **Business Rule**: Readings are time-series data for historical analysis and Excel export

### 7. Station_Information → MQTT_Task_Logs (One-to-Many)
- **Relationship**: One station generates multiple MQTT communication logs
- **Foreign Key**: mqtt_task_logs.station_id → station_information.station_id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Logs track MQTT communication for debugging and monitoring

## Query Patterns

### 1. Dashboard Queries
```sql
-- Get stations with latest readings and location
SELECT si.*, sr.temperature, sr.humidity, sr.reading_time as last_reading,
       s.name as state_name, dist.name as district_name,
       ds.status, ds.last_seen, ds.request_update
FROM station_information si
LEFT JOIN sensor_readings sr ON si.station_id = sr.station_id
LEFT JOIN states s ON si.state_id = s.id
LEFT JOIN districts dist ON si.district_id = dist.id
LEFT JOIN device_status ds ON si.station_id = ds.station_id
WHERE sr.id = (
    SELECT MAX(id) FROM sensor_readings WHERE station_id = si.station_id
) AND si.station_active = TRUE;

-- Get historical data for Excel export
SELECT sr.temperature, sr.humidity, sr.rssi, sr.battery_voltage, 
       sr.reading_time, sr.web_triggered, sr.created_at,
       si.station_name, si.station_id
FROM sensor_readings sr
JOIN station_information si ON sr.station_id = si.station_id
WHERE sr.station_id = ? AND DATE(sr.reading_time) BETWEEN ? AND ?
ORDER BY sr.reading_time DESC;
```

### 2. Historical Data Queries
```sql
-- Get readings with station and location info for modal display
SELECT sr.*, si.station_name, si.station_id, s.name as state_name, 
       dist.name as district_name
FROM sensor_readings sr
JOIN station_information si ON sr.station_id = si.station_id
JOIN states s ON si.state_id = s.id
JOIN districts dist ON si.district_id = dist.id
WHERE sr.reading_time BETWEEN ? AND ?
ORDER BY sr.reading_time DESC;

-- Get sensor data with date filtering
SELECT * FROM sensor_readings 
WHERE station_id = ? AND DATE(reading_time) = CURDATE()
ORDER BY reading_time DESC;
```

### 3. Location Queries
```sql
-- Get stations by location with full details
SELECT si.*, s.name as state_name, dist.name as district_name,
       ds.status, ds.last_seen, dc.data_interval, dc.data_collection_time
FROM station_information si
JOIN states s ON si.state_id = s.id
JOIN districts dist ON si.district_id = dist.id
LEFT JOIN device_status ds ON si.station_id = ds.station_id
LEFT JOIN device_configurations dc ON si.station_id = dc.station_id
WHERE si.state_id = ? AND si.district_id = ? AND si.station_active = TRUE
ORDER BY si.station_name;

-- Get cascading location data for dropdowns
SELECT s.id, s.name as state_name, s.code
FROM states s
ORDER BY s.name;

SELECT d.id, d.name as district_name, d.district_code
FROM districts d
WHERE d.state_id = ?
ORDER BY d.name;
```

### 4. MQTT Communication Queries
```sql
-- Get MQTT logs for debugging
SELECT mtl.*, si.station_name
FROM mqtt_task_logs mtl
JOIN station_information si ON mtl.station_id = si.station_id
WHERE mtl.station_id = ? AND mtl.received_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY mtl.received_at DESC;

-- Monitor MQTT communication status
SELECT mtl.station_id, mtl.task_type, mtl.status, COUNT(*) as count,
       MAX(mtl.received_at) as last_activity
FROM mqtt_task_logs mtl
WHERE mtl.received_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY mtl.station_id, mtl.task_type, mtl.status
ORDER BY last_activity DESC;
```

## Data Integrity Constraints

### 1. Referential Integrity
- All foreign key relationships enforced
- CASCADE deletes for dependent data
- NULL constraints on required fields
- Station active boolean flag for soft deletion
- Request update boolean flag for tracking remote data requests
- Configuration update flag for MQTT configuration management

### 2. Business Rules
- Station IDs must be unique across system (VARCHAR, not auto-increment)
- API tokens must be unique and secure (64 character tokens)
- State/District combination must be valid
- Sensor readings must have valid station reference
- GPS coordinates validation (latitude: -90 to 90, longitude: -180 to 180)
- MQTT task logs track communication for debugging and monitoring

### 3. Data Validation
- Temperature stored as DECIMAL(5,2) for precision (-99.99 to 999.99°C)
- Humidity stored as DECIMAL(5,2) for percentage (0.00 to 100.00%)
- RSSI stored as INTEGER for WiFi signal strength (dBm values)
- Battery voltage as DECIMAL(4,2) for precision (0.00 to 99.99V)
- Timestamps in Asia/Kuala_Lumpur timezone
- Boolean flags for web_triggered readings vs scheduled
- Boolean flags for request_update to track pending data requests
- ENUM constraints for device status (online/offline/maintenance)
- ENUM constraints for MQTT task types and directions

## Performance Considerations

### 1. Indexing Strategy
- Primary keys on all tables
- Foreign key indexes for joins
- Composite index on (station_id, created_at) for time-series queries
- Composite index on (station_id, reading_time) for historical data
- Unique indexes on business keys (email, station_id, api_token)
- Performance indexes for MQTT logs (station_id, task_type), (status, received_at)

### 2. Query Optimization
- Time-series partitioning consideration for large datasets
- Efficient pagination for historical data
- Optimized dashboard queries with proper indexing

### 3. Data Archival
- Consider archival strategy for old sensor readings and MQTT logs
- Backup and recovery procedures
- Data retention policies for compliance
- MQTT logs may require periodic cleanup for performance