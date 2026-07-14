<?php

namespace Database\Factories;

use App\Models\FuelType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelType>
 */
class FuelTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->randomElement(['Pertalite', 'Pertamax', 'Dexlite', 'Revvo 92', 'BP 92', 'Shell Super']).' '.fake()->unique()->bothify('??')),
            'type' => fake()->randomElement(array_keys(FuelType::TYPES)),
            'price' => fake()->numberBetween(9000, 18000),
            'merk' => fake()->randomElement(FuelType::MERKS),
        ];
    }
}
