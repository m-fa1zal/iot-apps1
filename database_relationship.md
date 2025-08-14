# Database Relationship Documentation - IoT Sensor Management System

## Entity Relationship Diagram (ERD)

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     USERS       │       │    DEVICES      │       │ SENSOR_READINGS │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id (PK)         │◄──────┤ id (PK)         │◄──────┤ id (PK)         │
│ name            │   1:M │ user_id (FK)    │   1:M │ device_id (FK)  │
│ email           │       │ name            │       │ distance        │
│ password        │       │ device_id       │       │ boot_count      │
│ telegram_chat_id│       │ api_token       │       │ web_triggered   │
│ created_at      │       │ state           │       │ created_at      │
│ updated_at      │       │ district        │       └─────────────────┘
└─────────────────┘       │ address         │
                          │ created_at      │
                          │ updated_at      │
                          └─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│     STATES      │       │   DISTRICTS     │
├─────────────────┤       ├─────────────────┤
│ id (PK)         │◄──────┤ id (PK)         │
│ name            │   1:M │ state_id (FK)   │
└─────────────────┘       │ name            │
                          └─────────────────┘
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
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Purpose**: Store user authentication and profile information
**Indexes**: 
- PRIMARY KEY (id)
- UNIQUE INDEX (email)

### 2. DEVICES Table
```sql
CREATE TABLE devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    device_id VARCHAR(100) UNIQUE NOT NULL,
    api_token VARCHAR(255) UNIQUE NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    address TEXT NULL,
    status ENUM('online', 'offline') DEFAULT 'offline',
    last_seen TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Purpose**: Store ESP32 device information and location details
**Relationships**: 
- Many-to-One with USERS (user_id → users.id)
- One-to-Many with SENSOR_READINGS

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (user_id)
- UNIQUE INDEX (device_id)
- UNIQUE INDEX (api_token)
- INDEX (state, district) for location filtering

### 3. SENSOR_READINGS Table
```sql
CREATE TABLE sensor_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    distance DECIMAL(8,2) NOT NULL COMMENT 'Distance in cm',
    boot_count INT UNSIGNED NOT NULL DEFAULT 0,
    web_triggered BOOLEAN DEFAULT FALSE COMMENT 'Manual vs scheduled reading',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
);
```

**Purpose**: Store sensor measurement data from ESP32 devices
**Relationships**: 
- Many-to-One with DEVICES (device_id → devices.id)

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (device_id)
- INDEX (device_id, created_at) for time-series queries
- INDEX (created_at) for dashboard queries

### 4. STATES Table (Reference Data)
```sql
CREATE TABLE states (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL
);
```

**Purpose**: Malaysian states reference data
**Relationships**: One-to-Many with DISTRICTS

### 5. DISTRICTS Table (Reference Data)
```sql
CREATE TABLE districts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
);
```

**Purpose**: Malaysian districts reference data
**Relationships**: Many-to-One with STATES

## Relationship Details

### 1. Users → Devices (One-to-Many)
- **Relationship**: One user can own multiple devices
- **Foreign Key**: devices.user_id → users.id
- **Cascade**: ON DELETE CASCADE (delete user's devices when user deleted)
- **Business Rule**: All authenticated users can manage all devices (no ownership restrictions)

### 2. Devices → Sensor_Readings (One-to-Many)
- **Relationship**: One device generates multiple sensor readings over time
- **Foreign Key**: sensor_readings.device_id → devices.id
- **Cascade**: ON DELETE CASCADE (delete readings when device deleted)
- **Business Rule**: Readings are time-series data for charts and historical analysis

### 3. States → Districts (One-to-Many)
- **Relationship**: One state contains multiple districts
- **Foreign Key**: districts.state_id → states.id
- **Cascade**: ON DELETE CASCADE
- **Business Rule**: Used for location cascading dropdowns

## Query Patterns

### 1. Dashboard Queries
```sql
-- Get devices with latest readings
SELECT d.*, sr.distance, sr.created_at as last_reading
FROM devices d
LEFT JOIN sensor_readings sr ON d.id = sr.device_id
WHERE sr.id = (
    SELECT MAX(id) FROM sensor_readings WHERE device_id = d.id
);

-- Get 24-hour chart data for device
SELECT DATE_FORMAT(created_at, '%H:%i') as time, distance
FROM sensor_readings 
WHERE device_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at;
```

### 2. Historical Data Queries
```sql
-- Get readings with device and location info
SELECT sr.*, d.name, d.state, d.district
FROM sensor_readings sr
JOIN devices d ON sr.device_id = d.id
WHERE sr.created_at BETWEEN ? AND ?
ORDER BY sr.created_at DESC;
```

### 3. Location Queries
```sql
-- Get devices by location
SELECT * FROM devices 
WHERE state = ? AND district = ?
ORDER BY name;

-- Get cascading location data
SELECT s.name as state, d.name as district
FROM states s
JOIN districts d ON s.id = d.state_id
ORDER BY s.name, d.name;
```

## Data Integrity Constraints

### 1. Referential Integrity
- All foreign key relationships enforced
- CASCADE deletes for dependent data
- NULL constraints on required fields

### 2. Business Rules
- Device IDs must be unique across system
- API tokens must be unique and secure
- Email addresses must be unique for users
- Sensor readings must have valid device reference

### 3. Data Validation
- Distance values stored as DECIMAL(8,2) for precision
- Timestamps in Asia/Kuala_Lumpur timezone
- Boolean flags for web_triggered readings
- ENUM constraints for device status

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