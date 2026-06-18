<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['Box', 'Pickup', 'Truck']),
            'entry_year' => (string) fake()->numberBetween(2018, 2026),
            'mileage' => (string) fake()->numberBetween(0, 250000),
            'plat_new' => strtoupper(fake()->unique()->bothify('? #### ??')),
            'status' => 'ACTIVE',
            'fuel_type' => 'BBM',
            'mtc' => fake()->randomElement(['YES', 'NO']),
            'desc' => fake()->optional()->sentence(),
            'isprivate' => fake()->boolean(),
        ];
    }
}
