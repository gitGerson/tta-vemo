<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Box', 'Pickup', 'Truck', 'Van', 'Trailer', 'Dump Truck', 'Tronton', 'Engkel'];

        foreach ($names as $i => $name) {
            VehicleType::updateOrCreate(
                ['code' => 'VT-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)],
                ['name' => strtoupper($name), 'descriptions' => 'Kendaraan tipe '.$name.'.'],
            );
        }
    }
}
