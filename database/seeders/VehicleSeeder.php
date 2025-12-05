<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'type' => 'Truck Box',
                'entry_year' => '2020',
                'mileage' => '150000',
                'plat_new' => 'B 1234 CD',
                'plat_old' => 'B 9876 CD',
                'status' => 'ACTIVE',
                'mtc' => 'NO',
                'desc' => 'Unit utama distribusi Jabodetabek.',
                'isprivate' => true,
            ],
            [
                'type' => 'Pickup',
                'entry_year' => '2019',
                'mileage' => '98000',
                'plat_new' => 'D 4567 EF',
                'plat_old' => null,
                'status' => 'ACTIVE',
                'mtc' => 'YES',
                'desc' => 'Sedang penjadwalan maintenance.',
                'isprivate' => false,
            ],
            [
                'type' => 'Van',
                'entry_year' => '2021',
                'mileage' => '62000',
                'plat_new' => 'L 8899 GH',
                'plat_old' => 'L 1122 GH',
                'status' => 'PASSIVE',
                'mtc' => 'NO',
                'desc' => 'Cadangan operasional.',
                'isprivate' => true,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(['plat_new' => $vehicle['plat_new']], $vehicle);
        }
    }
}
