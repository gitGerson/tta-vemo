<?php

namespace Database\Seeders;

use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelTypeSeeder extends Seeder
{
    public function run(): void
    {
        $fuelTypes = [
            [
                'name' => 'Solar',
                'price' => 6800,
            ],
            [
                'name' => 'Pertalite',
                'price' => 10000,
            ],
            [
                'name' => 'Pertamax',
                'price' => 12900,
            ],
        ];

        foreach ($fuelTypes as $fuelType) {
            FuelType::updateOrCreate(
                [
                    'name' => $fuelType['name'],
                ],
                $fuelType
            );
        }
    }
}
