<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Record;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Record>
 */
class RecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startKm = fake()->numberBetween(0, 200000);
        $finishKm = $startKm + fake()->numberBetween(10, 800);
        $totals = $finishKm - $startKm;
        $bbm = fake()->numberBetween(5, 80);
        $unitPrice = fake()->numberBetween(10000, 20000);

        return [
            'driver_1' => fake()->name(),
            'driver_2' => fake()->optional()->name(),
            'vehicle_id' => Vehicle::factory(),
            'no_pol' => strtoupper(fake()->bothify('? #### ??')),
            'destination' => fake()->city(),
            'company_id' => Company::factory(),
            'fuel_type_id' => null,
            'start_km' => (string) $startKm,
            'finish_km' => (string) $finishKm,
            'totals' => (string) $totals,
            'bbm' => (string) $bbm,
            'fuel_unit_price' => (string) $unitPrice,
            'idr' => number_format($unitPrice * $bbm, 2, '.', ''),
            'scale' => (string) (int) ($totals / max($bbm, 1)),
            'date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ];
    }
}
