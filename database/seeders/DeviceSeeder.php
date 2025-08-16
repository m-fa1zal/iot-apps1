<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\State;
use App\Models\District;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some states and districts for reference
        $states = State::all();
        if ($states->isEmpty()) {
            $this->command->info('No states found. Please run StatesSeeder first.');
            return;
        }

        $devices = [
            [
                'station_name' => 'Weather Station Alpha',
                'mac_address' => 'AA:BB:CC:DD:EE:F1',
                'address' => 'Industrial Area, Block A',
                'gps_latitude' => 3.1390,
                'gps_longitude' => 101.6869,
                'status' => 'online',
                'data_interval_minutes' => 5,
                'data_collection_time_minutes' => 30,
            ],
            [
                'station_name' => 'Environmental Monitor Beta',
                'mac_address' => 'BB:CC:DD:EE:FF:A2',
                'address' => 'City Center, Main Street',
                'gps_latitude' => 3.1470,
                'gps_longitude' => 101.6958,
                'status' => 'online',
                'data_interval_minutes' => 10,
                'data_collection_time_minutes' => 45,
            ],
            [
                'station_name' => 'Air Quality Sensor Gamma',
                'mac_address' => 'CC:DD:EE:FF:AA:B3',
                'address' => 'Residential Area, Park View',
                'gps_latitude' => 3.1520,
                'gps_longitude' => 101.7000,
                'status' => 'offline',
                'data_interval_minutes' => 15,
                'data_collection_time_minutes' => 60,
            ],
            [
                'station_name' => 'Smart Sensor Delta',
                'mac_address' => 'DD:EE:FF:AA:BB:C4',
                'address' => 'Technology Park, Building 5',
                'gps_latitude' => 3.1350,
                'gps_longitude' => 101.6800,
                'status' => 'maintenance',
                'data_interval_minutes' => 2,
                'data_collection_time_minutes' => 30,
            ],
            [
                'station_name' => 'Climate Monitor Echo',
                'mac_address' => 'EE:FF:AA:BB:CC:D5',
                'address' => 'University Campus, Science Block',
                'gps_latitude' => 3.1600,
                'gps_longitude' => 101.7100,
                'status' => 'online',
                'data_interval_minutes' => 3,
                'data_collection_time_minutes' => 20,
            ]
        ];

        foreach ($devices as $index => $deviceData) {
            // Get random state and district
            $state = $states->random();
            $districts = District::where('state_id', $state->id)->get();
            
            if ($districts->isEmpty()) {
                continue; // Skip if no districts found for this state
            }
            
            $district = $districts->random();
            
            // Generate station_id manually since district_code might be null
            $stationId = 'ST-' . strtoupper(Str::random(3)) . '-' . (1000 + $index + 1);
            
            Device::create([
                'station_name' => $deviceData['station_name'],
                'station_id' => $stationId,
                'api_token' => Str::random(64),
                'mac_address' => $deviceData['mac_address'],
                'data_interval_minutes' => $deviceData['data_interval_minutes'],
                'data_collection_time_minutes' => $deviceData['data_collection_time_minutes'],
                'state_id' => $state->id,
                'district_id' => $district->id,
                'address' => $deviceData['address'],
                'gps_latitude' => $deviceData['gps_latitude'],
                'gps_longitude' => $deviceData['gps_longitude'],
                'status' => $deviceData['status'],
                'station_active' => true,
                'last_seen' => $deviceData['status'] === 'online' ? now()->subMinutes(rand(1, 30)) : null,
            ]);
        }

        $this->command->info('Created ' . count($devices) . ' devices successfully.');
    }
}
