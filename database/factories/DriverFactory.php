<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function withRandomImage(): static
    {
        return $this->state(fn () => [
            'image' => 'https://i.pravatar.cc/300?img='.fake()->unique()->numberBetween(1, 70),
        ]);
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nik' => fake()->unique()->numerify('##########'),
            'address' => fake()->address(),
            'no_identity' => fake()->unique()->numerify('################'),
            'residence' => fake()->city(),
            'date_birth' => fake()->dateTimeBetween('-55 years', '-21 years')->format('Y-m-d'),
            'no_phone_1' => fake()->unique()->numerify('08##########'),
            'no_phone_2' => fake()->unique()->numerify('08##########'),
            'entry_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'out_date' => null,
            'status' => 'ACTIVE',
        ];
    }
}
