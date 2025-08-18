<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            [
                'station_id' => 'KL001',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:01',
                'data_interval' => 2,
                'data_collection_time' => 30,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'PJ002',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:02',
                'data_interval' => 3,
                'data_collection_time' => 45,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'SA003',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:03',
                'data_interval' => 2,
                'data_collection_time' => 30,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'JB004',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:04',
                'data_interval' => 4,
                'data_collection_time' => 60,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'PG005',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:05',
                'data_interval' => 2,
                'data_collection_time' => 30,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'KK006',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:06',
                'data_interval' => 5,
                'data_collection_time' => 90,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'KC007',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:07',
                'data_interval' => 3,
                'data_collection_time' => 45,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'IP008',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:08',
                'data_interval' => 2,
                'data_collection_time' => 30,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'ML009',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:09',
                'data_interval' => 4,
                'data_collection_time' => 60,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_id' => 'KT010',
                'api_token' => Str::random(64),
                'mac_address' => '24:6F:28:AB:CD:10',
                'data_interval' => 3,
                'data_collection_time' => 45,
                'configuration_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('device_configurations')->insert($configurations);
    }
}