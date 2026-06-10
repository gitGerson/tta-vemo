<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<VehiclePhoto>
 */
class VehiclePhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'period' => Carbon::parse(fake()->dateTimeBetween('-1 year', 'now'))->startOfMonth()->toDateString(),
            'photos' => ['vehicle_photos/'.fake()->uuid().'.jpg'],
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Pin the record to the current month.
     */
    public function currentMonth(): static
    {
        return $this->state(fn (): array => [
            'period' => now()->startOfMonth()->toDateString(),
        ]);
    }
}
