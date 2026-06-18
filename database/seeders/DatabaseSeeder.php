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

        User::factory()->create([
            'username' => 'stagingpurpose',
            'name' => 'stagingpurpose',
            'password' => bcrypt('P@ssw0rd1938'),
        ]);

        // run RolePermissionSeeder
        $this->call(RolePermissionSeeder::class);
    }
}
