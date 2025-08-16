<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Johor', 'state_code' => 'JHR'],
            ['name' => 'Kedah', 'state_code' => 'KDH'],
            ['name' => 'Kelantan', 'state_code' => 'KTN'],
            ['name' => 'Kuala Lumpur', 'state_code' => 'KUL'],
            ['name' => 'Labuan', 'state_code' => 'LBN'],
            ['name' => 'Melaka', 'state_code' => 'MLK'],
            ['name' => 'Negeri Sembilan', 'state_code' => 'NSN'],
            ['name' => 'Pahang', 'state_code' => 'PHG'],
            ['name' => 'Penang', 'state_code' => 'PNG'],
            ['name' => 'Perak', 'state_code' => 'PRK'],
            ['name' => 'Perlis', 'state_code' => 'PLS'],
            ['name' => 'Putrajaya', 'state_code' => 'PJY'],
            ['name' => 'Sabah', 'state_code' => 'SBH'],
            ['name' => 'Sarawak', 'state_code' => 'SWK'],
            ['name' => 'Selangor', 'state_code' => 'SGR'],
            ['name' => 'Terengganu', 'state_code' => 'TRG'],
        ];

        foreach ($states as $state) {
            DB::table('states')->updateOrInsert(
                ['state_code' => $state['state_code']],
                [
                    'name' => $state['name'],
                    'state_code' => $state['state_code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}