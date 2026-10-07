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
        $this->call([
            RolePermissionSeeder::class,
            VemoRoleSeeder::class,
            VemoDummyUserSeeder::class,
            CompanySeeder::class,
            VehicleBrandSeeder::class,
            VehicleTypeSeeder::class,
            FuelTypeSeeder::class,
            VehicleSeeder::class,
            TypeMaintenanceSeeder::class,
            MaintenanceSeeder::class,
            DriverSeeder::class,
            DestinationSeeder::class,
            RecordSeeder::class,
        ]);
    }
}
