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
            ['name' => 'Johor', 'code' => 'JHR'],
            ['name' => 'Kedah', 'code' => 'KDH'],
            ['name' => 'Kelantan', 'code' => 'KTN'],
            ['name' => 'Kuala Lumpur', 'code' => 'KUL'],
            ['name' => 'Labuan', 'code' => 'LBN'],
            ['name' => 'Melaka', 'code' => 'MLK'],
            ['name' => 'Negeri Sembilan', 'code' => 'NSN'],
            ['name' => 'Pahang', 'code' => 'PHG'],
            ['name' => 'Penang', 'code' => 'PNG'],
            ['name' => 'Perak', 'code' => 'PRK'],
            ['name' => 'Perlis', 'code' => 'PLS'],
            ['name' => 'Putrajaya', 'code' => 'PJY'],
            ['name' => 'Sabah', 'code' => 'SBH'],
            ['name' => 'Sarawak', 'code' => 'SWK'],
            ['name' => 'Selangor', 'code' => 'SGR'],
            ['name' => 'Terengganu', 'code' => 'TRG'],
        ];

        foreach ($states as $state) {
            DB::table('states')->updateOrInsert(
                ['code' => $state['code']],
                [
                    'name' => $state['name'],
                    'code' => $state['code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}