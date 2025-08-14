<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial admin user
        User::updateOrCreate(
            ['email' => 'admin@iot-apps.local'],
            [
                'name' => 'Admin User',
                'email' => 'admin@iot-apps.local',
                'password' => Hash::make('password123'),
                'telegram_chat_id' => null,
                'email_verified_at' => now(),
            ]
        );

        // Create test user
        User::updateOrCreate(
            ['email' => 'test@iot-apps.local'],
            [
                'name' => 'Test User',
                'email' => 'test@iot-apps.local',
                'password' => Hash::make('password123'),
                'telegram_chat_id' => '123456789',
                'email_verified_at' => now(),
            ]
        );

        // Create demo user for Selangor
        User::updateOrCreate(
            ['email' => 'demo@iot-apps.local'],
            [
                'name' => 'Demo User',
                'email' => 'demo@iot-apps.local',
                'password' => Hash::make('password123'),
                'telegram_chat_id' => '987654321',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Initial users created:');
        $this->command->info('Admin: admin@iot-apps.local / password123');
        $this->command->info('Test: test@iot-apps.local / password123');
        $this->command->info('Demo: demo@iot-apps.local / password123');
    }
}