<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (! User::where('username', 'stagingpurpose')->exists()) {
            User::factory()->create([
                'username' => 'stagingpurpose',
                'name' => 'stagingpurpose',
                'password' => bcrypt('P@ssw0rd1938'),
            ]);
        }

        $this->call([
            RolePermissionSeeder::class,
            CompanySeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            DestinationSeeder::class,
            TypeMaintenanceSeeder::class,
            FuelTypeSeeder::class,
        ]);
    }
}
