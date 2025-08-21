<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active stations
        $stations = DB::table('station_information')->where('station_active', true)->get();
        
        if ($stations->isEmpty()) {
            $this->command->info('No active stations found. Please run StationInformationSeeder first.');
            return;
        }

        $this->command->info('🌡️ Generating sensor readings for ' . $stations->count() . ' stations...');

        // Set base date to today
        $baseDate = Carbon::today();
        $totalReadings = 0;
        $readings = [];
        $deviceStatusData = [];

        foreach ($stations as $station) {
            // Generate 8 hours of data (from 08:00 to 16:00) with readings every 10-30 minutes
            $startTime = $baseDate->copy()->setTime(8, 0, 0); // 8:00 AM
            $endTime = $baseDate->copy()->setTime(16, 0, 0);   // 4:00 PM
            
            $currentTime = $startTime->copy();
            $stationReadings = 0;
            $lastReadingTime = $startTime->copy();
            
            while ($currentTime->lte($endTime)) {
                // Generate realistic sensor data
                $temperature = $this->generateTemperature($currentTime);
                $humidity = $this->generateHumidity($currentTime, $temperature);
                $rssi = $this->generateRSSI(); // RSSI range for MQTT/WiFi
                $batteryVoltage = $this->generateBatteryVoltage();
                
                $readings[] = [
                    'station_id' => $station->station_id,
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'rssi' => $rssi,
                    'battery_voltage' => $batteryVoltage,
                    'reading_time' => $currentTime->copy(),
                    'web_triggered' => rand(0, 10) === 0, // 10% chance of manual reading
                    'created_at' => $currentTime->copy()->addSeconds(rand(1, 300)), // Slight delay from reading time
                    'updated_at' => $currentTime->copy()->addSeconds(rand(1, 300)),
                ];
                
                $lastReadingTime = $currentTime->copy();
                $stationReadings++;
                
                // Add random interval between 10-30 minutes for next reading
                $intervalMinutes = rand(10, 30);
                $currentTime->addMinutes($intervalMinutes);
            }
            
            // Create device status entry for each station
            $deviceStatusData[] = [
                'station_id' => $station->station_id,
                'status' => $this->getRandomDeviceStatus(),
                'request_update' => rand(0, 1),
                'last_seen' => $lastReadingTime->addMinutes(rand(1, 15)), // Last seen within 15 minutes of last reading
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $totalReadings += $stationReadings;
            $this->command->info("📊 Generated {$stationReadings} readings for station: {$station->station_name}");
        }

        // Insert all readings and device status in batches
        $this->command->info('💾 Inserting sensor readings into database...');
        
        // Insert in chunks to avoid memory issues with large datasets
        $chunks = array_chunk($readings, 100);
        foreach ($chunks as $chunk) {
            DB::table('sensor_readings')->insert($chunk);
        }
        
        $this->command->info('📡 Creating device status records...');
        DB::table('device_status')->insert($deviceStatusData);
        
        $this->command->info("✅ Successfully created {$totalReadings} sensor readings across " . $stations->count() . " stations.");
        $this->command->info("📱 Created " . count($deviceStatusData) . " device status records.");
    }

    /**
     * Generate realistic temperature based on time of day
     */
    private function generateTemperature($time)
    {
        $hour = $time->hour;
        
        // Temperature varies throughout the day (morning cooler, afternoon warmer)
        $baseTemp = match(true) {
            $hour >= 6 && $hour < 9 => rand(24, 28),   // Morning: 24-28°C
            $hour >= 9 && $hour < 12 => rand(28, 32),  // Late morning: 28-32°C
            $hour >= 12 && $hour < 15 => rand(32, 36), // Afternoon: 32-36°C (hottest)
            $hour >= 15 && $hour < 18 => rand(30, 34), // Late afternoon: 30-34°C
            default => rand(26, 30), // Other times: 26-30°C
        };
        
        // Add some random variation
        return $baseTemp + (rand(-200, 200) / 100); // ±2°C variation
    }

    /**
     * Generate humidity inversely related to temperature
     */
    private function generateHumidity($time, $temperature)
    {
        // Higher temperature generally means lower humidity
        $baseHumidity = match(true) {
            $temperature < 26 => rand(70, 90),  // Cool temp = high humidity
            $temperature < 30 => rand(60, 80),  // Moderate temp = moderate humidity
            $temperature < 34 => rand(50, 70),  // Warm temp = lower humidity
            default => rand(40, 60),            // Hot temp = low humidity
        };
        
        // Add time-based variation (morning usually more humid)
        $hour = $time->hour;
        if ($hour >= 6 && $hour < 10) {
            $baseHumidity += rand(5, 15); // Morning boost
        }
        
        // Add random variation and ensure it's within valid range
        $humidity = $baseHumidity + rand(-10, 10);
        return max(20, min(100, $humidity)); // Clamp between 20-100%
    }

    /**
     * Generate battery voltage (slowly decreasing over time)
     */
    private function generateBatteryVoltage()
    {
        // Battery voltage typically 3.0V to 4.2V for Li-ion
        // Generate realistic values with slight random variation
        $baseVoltage = rand(320, 410) / 100; // 3.20V to 4.10V
        return round($baseVoltage, 2);
    }

    /**
     * Generate RSSI values for MQTT/WiFi connection
     */
    private function generateRSSI()
    {
        // RSSI range for WiFi/MQTT: -30 to -90 dBm
        // -30 to -50: Excellent signal
        // -50 to -60: Good signal  
        // -60 to -70: Fair signal
        // -70 to -80: Weak signal
        // -80 to -90: Very weak signal
        return rand(-90, -30);
    }

    /**
     * Get random device status
     */
    private function getRandomDeviceStatus()
    {
        $statuses = ['online', 'offline', 'maintenance'];
        $weights = [70, 25, 5]; // 70% online, 25% offline, 5% maintenance
        
        $random = rand(1, 100);
        if ($random <= $weights[0]) return $statuses[0];
        if ($random <= $weights[0] + $weights[1]) return $statuses[1];
        return $statuses[2];
    }

}
