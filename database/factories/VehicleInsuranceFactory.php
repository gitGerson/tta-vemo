<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleInsurance>
 */
class VehicleInsuranceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 years', 'now');
        $endDate = (clone $startDate)->modify('+1 year');

        return [
            'code' => strtoupper(fake()->unique()->bothify('INS-####')),
            'vehicle_id' => Vehicle::factory(),
            'insurer_name' => fake()->randomElement(['Jasindo', 'Astra Buana', 'Sinar Mas', 'Allianz']),
            'policy_number' => strtoupper(fake()->bothify('POL-########')),
            'insurance_type' => fake()->randomElement(['All Risk', 'TLO']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'coverage_value' => fake()->numberBetween(50_000_000, 500_000_000),
            'premium' => fake()->numberBetween(1_000_000, 20_000_000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
