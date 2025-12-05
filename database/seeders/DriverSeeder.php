<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'image' => null,
                'nik' => '3271012000010001',
                'name' => 'Budi Santoso',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'no_identity' => 'DRV001',
                'residence' => 'Jakarta',
                'date_birth' => '1990-05-12',
                'no_phone_1' => '08120001001',
                'no_phone_2' => '08130001001',
                'entry_date' => '2020-01-15',
                'out_date' => null,
                'status' => 'ACTIVE',
            ],
            [
                'image' => null,
                'nik' => '3271012000020002',
                'name' => 'Siti Lestari',
                'address' => 'Jl. Melati No. 5, Bandung',
                'no_identity' => 'DRV002',
                'residence' => 'Bandung',
                'date_birth' => '1988-09-03',
                'no_phone_1' => '08120002002',
                'no_phone_2' => '08130002002',
                'entry_date' => '2019-03-10',
                'out_date' => null,
                'status' => 'ACTIVE',
            ],
            [
                'image' => null,
                'nik' => '3271012000030003',
                'name' => 'Rudi Hartono',
                'address' => 'Jl. Kenanga No. 2, Surabaya',
                'no_identity' => 'DRV003',
                'residence' => 'Surabaya',
                'date_birth' => '1985-12-21',
                'no_phone_1' => '08120003003',
                'no_phone_2' => '08130003003',
                'entry_date' => '2018-07-01',
                'out_date' => null,
                'status' => 'PASSIVE',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::updateOrCreate(['no_identity' => $driver['no_identity']], $driver);
        }
    }
}
