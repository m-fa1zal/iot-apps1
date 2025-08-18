<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting IoT Apps Database Seeding...');
        
        // Seed Malaysian States and Districts (Location Reference Data)
        $this->command->info('📍 Seeding Malaysian states...');
        $this->call(StatesSeeder::class);
        
        $this->command->info('🏙️ Seeding Malaysian districts...');
        $this->call(DistrictsSeeder::class);
        
        // Seed Initial Users
        $this->command->info('👥 Seeding initial users...');
        $this->call(UserSeeder::class);
        
        // Seed Station Information
        $this->command->info('🏢 Seeding station information...');
        $this->call(StationInformationSeeder::class);
        
        // Seed Device Configurations
        $this->command->info('⚙️ Seeding device configurations...');
        $this->call(DeviceConfigurationSeeder::class);
        
        
        // Seed Sensor Readings (8 hours of data for today)
        $this->command->info('📊 Seeding sensor readings...');
        $this->call(SensorReadingSeeder::class);
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('🎯 System is ready with:');
        $this->command->info('   • 16 Malaysian states');
        $this->command->info('   • 190+ districts across Malaysia');
        $this->command->info('   • 3 initial users (admin, test, demo)');
        $this->command->info('   • 10 monitoring stations across Malaysia');
        $this->command->info('   • 10 device configurations with API tokens');
        $this->command->info('   • 8 hours of sensor readings data');
        $this->command->info('');
        $this->command->info('🚀 You can now start using the IoT Apps system!');
    }
}
