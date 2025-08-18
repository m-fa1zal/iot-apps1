<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StationInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            [
                'station_name' => 'Kuala Lumpur Central Station',
                'station_id' => 'KL001',
                'state_id' => 4, // Kuala Lumpur (correct ID)
                'district_id' => 1, // First district
                'address' => 'Jalan Sultan Hishamuddin, 50621 Kuala Lumpur',
                'gps_latitude' => 3.134100,
                'gps_longitude' => 101.686600,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Petaling Jaya Monitoring Station',
                'station_id' => 'PJ002',
                'state_id' => 15, // Selangor (correct ID)
                'district_id' => 2, // Second district
                'address' => 'Jalan University, 46200 Petaling Jaya, Selangor',
                'gps_latitude' => 3.107400,
                'gps_longitude' => 101.593400,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Shah Alam Industrial Station',
                'station_id' => 'SA003',
                'state_id' => 15, // Selangor (correct ID)
                'district_id' => 3, // Third district
                'address' => 'Persiaran Perbandaran, 40000 Shah Alam, Selangor',
                'gps_latitude' => 3.085300,
                'gps_longitude' => 101.532100,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Johor Bahru Coastal Station',
                'station_id' => 'JB004',
                'state_id' => 1, // Johor
                'district_id' => 4, // Fourth district
                'address' => 'Jalan Wong Ah Fook, 80000 Johor Bahru, Johor',
                'gps_latitude' => 1.464100,
                'gps_longitude' => 103.761000,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Penang Georgetown Station',
                'station_id' => 'PG005',
                'state_id' => 9, // Penang (correct ID)
                'district_id' => 5, // Fifth district
                'address' => 'Lebuh Light, 10200 George Town, Pulau Pinang',
                'gps_latitude' => 5.414100,
                'gps_longitude' => 100.329700,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Kota Kinabalu Environmental Station',
                'station_id' => 'KK006',
                'state_id' => 13, // Sabah (correct ID)
                'district_id' => 6, // Sixth district
                'address' => 'Jalan Gaya, 88000 Kota Kinabalu, Sabah',
                'gps_latitude' => 5.975400,
                'gps_longitude' => 116.095800,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Kuching Research Station',
                'station_id' => 'KC007',
                'state_id' => 14, // Sarawak (correct ID)
                'district_id' => 7, // Seventh district
                'address' => 'Jalan Padungan, 93100 Kuching, Sarawak',
                'gps_latitude' => 1.555300,
                'gps_longitude' => 110.346900,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Ipoh Valley Station',
                'station_id' => 'IP008',
                'state_id' => 10, // Perak (correct ID)
                'district_id' => 8, // Eighth district
                'address' => 'Jalan Sultan Idris Shah, 30000 Ipoh, Perak',
                'gps_latitude' => 4.597200,
                'gps_longitude' => 101.090400,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Melaka Heritage Station',
                'station_id' => 'ML009',
                'state_id' => 5, // Melaka (correct ID)
                'district_id' => 9, // Ninth district
                'address' => 'Jalan Hang Tuah, 75300 Melaka',
                'gps_latitude' => 2.196300,
                'gps_longitude' => 102.248600,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'station_name' => 'Kuantan Coastal Monitoring',
                'station_id' => 'KT010',
                'state_id' => 8, // Pahang (correct ID)
                'district_id' => 10, // Tenth district
                'address' => 'Jalan Mahkota, 25000 Kuantan, Pahang',
                'gps_latitude' => 3.807700,
                'gps_longitude' => 103.326000,
                'station_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('station_information')->insert($stations);
    }
}