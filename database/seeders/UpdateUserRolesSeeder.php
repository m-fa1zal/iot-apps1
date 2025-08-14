<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update admin user - first user or admin@iot-apps.local
        $adminUser = User::where('email', 'admin@iot-apps.local')
                        ->orWhere('id', 1)
                        ->first();
        
        if ($adminUser) {
            $adminUser->update(['role' => 'admin']);
            $this->command->info("Updated user '{$adminUser->name}' to admin role");
        }
        
        // Update all other users to 'user' role
        User::where('role', 'user')
            ->orWhereNull('role')
            ->where('id', '!=', $adminUser?->id)
            ->update(['role' => 'user']);
            
        $this->command->info('Updated all other users to user role');
    }
}
