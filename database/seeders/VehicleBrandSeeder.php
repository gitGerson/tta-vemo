<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use Illuminate\Database\Seeder;

class VehicleBrandSeeder extends Seeder
{
    public function run(): void
    {
        // Logo filled from the internet (stored as a URL — VehicleBrand::logoUrl()
        // passes http* through). Real SVG logos from SimpleIcons where available;
        // truck brands not in that set use a text logo from placehold.co.
        $rows = [
            ['name' => 'Toyota', 'icon' => 'toyota'],
            ['name' => 'Mitsubishi', 'icon' => 'mitsubishi'],
            ['name' => 'Hino', 'icon' => null],
            ['name' => 'Isuzu', 'icon' => null],
            ['name' => 'Daihatsu', 'icon' => null],
            ['name' => 'Suzuki', 'icon' => 'suzuki'],
            ['name' => 'Honda', 'icon' => 'honda'],
        ];

        foreach ($rows as $i => $row) {
            $logo = $row['icon'] !== null
                ? 'https://cdn.simpleicons.org/'.$row['icon']
                : 'https://placehold.co/240x120/png?text='.rawurlencode(strtoupper($row['name']));

            VehicleBrand::updateOrCreate(
                ['code' => 'VB'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'name' => strtoupper($row['name']),
                    'logo' => $logo,
                    'descriptions' => 'Merek kendaraan '.$row['name'].'.',
                ],
            );
        }
    }
}
