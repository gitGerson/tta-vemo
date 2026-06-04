<?php

namespace Database\Factories;

use App\Models\VehicleBrand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleBrand>
 */
class VehicleBrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'code' => strtoupper(fake()->unique()->bothify('VB###')),
            'name' => strtoupper($name),
            'logo' => null,
            'descriptions' => fake()->sentence(),
        ];
    }
}
