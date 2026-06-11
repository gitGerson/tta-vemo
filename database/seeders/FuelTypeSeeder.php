<?php

namespace Database\Seeders;

use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelTypeSeeder extends Seeder
{
    /**
     * Seeded from the fuel price matrix (isibens.in): rows are fuel grades
     * (RON = bensin/gasoline octane, CN = solar/diesel cetane), columns are
     * merks, cells are prices in IDR. Empty or zero cells mean the merk does
     * not list that grade and are skipped.
     */
    public function run(): void
    {
        // CSV column header => app merk (FuelType::MERKS).
        $merks = ['Pertamina' => 'Pertamina', 'Shell' => 'Shell', 'BP-AKR' => 'BP', 'Vivo' => 'Vivo'];

        $matrix = [
            'RON 90' => ['Pertamina' => 10000, 'Shell' => null, 'BP-AKR' => null, 'Vivo' => 0],
            'RON 92' => ['Pertamina' => 16250, 'Shell' => 0, 'BP-AKR' => 16670, 'Vivo' => 0],
            'RON 95' => ['Pertamina' => null, 'Shell' => 0, 'BP-AKR' => 17240, 'Vivo' => 17240],
            'RON 98' => ['Pertamina' => 20750, 'Shell' => 0, 'BP-AKR' => null, 'Vivo' => null],
            'CN 48' => ['Pertamina' => 6800, 'Shell' => null, 'BP-AKR' => null, 'Vivo' => null],
            'CN 51' => ['Pertamina' => 23000, 'Shell' => 24490, 'BP-AKR' => 25060, 'Vivo' => 25060],
            'CN 53' => ['Pertamina' => 24800, 'Shell' => null, 'BP-AKR' => null, 'Vivo' => null],
        ];

        foreach ($matrix as $grade => $prices) {
            $type = str_starts_with($grade, 'RON') ? 'bensin' : 'solar';

            foreach ($merks as $column => $merk) {
                $price = $prices[$column] ?? null;

                if ($price === null || $price <= 0) {
                    continue;
                }

                FuelType::updateOrCreate(
                    ['name' => $grade, 'merk' => $merk],
                    ['type' => $type, 'price' => $price],
                );
            }
        }
    }
}
