<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleType>
 */
class VehicleTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('VT-###')),
            'name' => strtoupper(fake()->unique()->randomElement(['Box', 'Pickup', 'Truck', 'Van', 'Trailer']).' '.fake()->bothify('??')),
            'descriptions' => fake()->optional()->sentence(),
        ];
    }
}
