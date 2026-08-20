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
            // A complete record has all four sides by default.
            'photos' => collect(VehiclePhoto::SIDES)
                ->keys()
                ->mapWithKeys(fn (string $side): array => [$side => 'vehicle_photos/'.fake()->uuid().'.jpg'])
                ->all(),
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

    /**
     * A partial record with only the first N sides filled (incomplete).
     */
    public function partial(int $sides = 2): static
    {
        return $this->state(fn (): array => [
            'photos' => collect(VehiclePhoto::SIDES)
                ->keys()
                ->take($sides)
                ->mapWithKeys(fn (string $side): array => [$side => 'vehicle_photos/'.fake()->uuid().'.jpg'])
                ->all(),
        ]);
    }
}
