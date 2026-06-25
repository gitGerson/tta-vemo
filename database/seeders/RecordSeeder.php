<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Driver;
use App\Models\Record;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::query()
            ->with('fuelType')
            ->where('plat_new', 'like', 'SEED %')
            ->where('status', 'ACTIVE')
            ->orderBy('plat_new')
            ->get();
        $drivers = Driver::query()->where('status', 'ACTIVE')->orderBy('id')->get();
        $destinations = $this->destinations();

        if ($vehicles->isEmpty() || $drivers->isEmpty() || $destinations->isEmpty()) {
            $this->command?->warn('RecordSeeder skipped. Seed active vehicles, active drivers, and destinations first.');

            return;
        }

        for ($index = 1; $index <= 10; $index++) {
            $vehicle = $vehicles[($index - 1) % $vehicles->count()];
            $driverOne = $drivers[($index - 1) % $drivers->count()];
            $driverTwo = $drivers[$index % $drivers->count()];
            $destination = $destinations[($index - 1) % $destinations->count()];
            $startKm = (int) $vehicle->mileage + (($index - 1) * 80);
            $totals = 120 + ($index * 15);
            $finishKm = $startKm + $totals;
            $bbm = 15 + $index;

            $record = Record::withTrashed()->updateOrCreate(
                [
                    'no_pol' => $vehicle->plat_new,
                    'date' => now()->subDays(10 - $index)->toDateString(),
                ],
                [
                    'driver_1' => $driverOne->name,
                    'driver_2' => $driverTwo->is($driverOne) ? null : $driverTwo->name,
                    'vehicle_id' => $vehicle->id,
                    'destination' => $destination->name,
                    'company_id' => $vehicle->company_id,
                    'fuel_type_id' => $vehicle->fuel_type_id,
                    'start_km' => (string) $startKm,
                    'finish_km' => (string) $finishKm,
                    'totals' => (string) $totals,
                    'bbm' => (string) $bbm,
                    'idr' => (string) ($vehicle->fuelType?->price ?? 0),
                    'scale' => number_format($totals / $bbm, 2, '.', ''),
                ],
            );

            if ($record->trashed()) {
                $record->restore();
            }
        }
    }

    /**
     * @return Collection<int, Destination>
     */
    private function destinations(): Collection
    {
        $destinations = Destination::query()->orderBy('name')->limit(10)->get();

        if ($destinations->isNotEmpty()) {
            return $destinations;
        }

        DB::table('indonesia_provinces')->updateOrInsert(
            ['code' => '31'],
            [
                'name' => 'DKI JAKARTA',
                'meta' => 'Seeded province fallback.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $rows = [
            ['code' => '9101', 'name' => 'SEED DESTINATION 1'],
            ['code' => '9102', 'name' => 'SEED DESTINATION 2'],
            ['code' => '9103', 'name' => 'SEED DESTINATION 3'],
        ];

        foreach ($rows as $row) {
            Destination::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'province_code' => '31',
                    'name' => $row['name'],
                    'meta' => 'Seeded destination fallback.',
                ],
            );
        }

        return Destination::query()->whereIn('code', array_column($rows, 'code'))->orderBy('code')->get();
    }
}
