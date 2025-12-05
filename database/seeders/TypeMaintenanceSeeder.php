<?php

namespace Database\Seeders;

use App\Models\TypeMaintenances;
use Illuminate\Database\Seeder;

class TypeMaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Service Berkala'],
            ['name' => 'Ganti Oli'],
            ['name' => 'Perbaikan Rem'],
            ['name' => 'Penggantian Ban'],
        ];

        foreach ($types as $type) {
            TypeMaintenances::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
