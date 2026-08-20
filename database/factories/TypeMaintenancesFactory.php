<?php

namespace Database\Factories;

use App\Models\TypeMaintenances;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypeMaintenances>
 */
class TypeMaintenancesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->words(3, true)),
            'jenis' => fake()->randomElement(['jasa', 'barang']),
        ];
    }
}
