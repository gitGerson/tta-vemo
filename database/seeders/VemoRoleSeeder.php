<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class VemoRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Kasir', 'Admin Maintenance', 'Manager', 'Supervisor'] as $name) {
            Role::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $name,
                    'guard_name' => 'web',
                    'is_active' => true,
                ],
            );
        }
    }
}
