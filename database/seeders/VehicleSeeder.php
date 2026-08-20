<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\FuelType;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleInsurance;
use App\Models\VehiclePhoto;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::query()->withoutGlobalScopes()->orderBy('id')->get();
        $fuelTypes = FuelType::query()->orderBy('id')->get();
        $vehicleTypes = VehicleType::query()->orderBy('id')->get();
        $vehicleBrands = VehicleBrand::query()->orderBy('id')->get();

        if ($companies->isEmpty() || $fuelTypes->isEmpty() || $vehicleTypes->isEmpty() || $vehicleBrands->isEmpty()) {
            $this->command?->warn('VehicleSeeder skipped. Seed companies, fuel types, vehicle types, and vehicle brands first.');

            return;
        }

        for ($index = 1; $index <= 10; $index++) {
            $company = $companies[($index - 1) % $companies->count()];
            $fuelType = $fuelTypes[($index - 1) % $fuelTypes->count()];
            $vehicleType = $vehicleTypes[($index - 1) % $vehicleTypes->count()];
            $vehicleBrand = $vehicleBrands[($index - 1) % $vehicleBrands->count()];

            $vehicle = Vehicle::withTrashed()->updateOrCreate(
                ['plat_new' => sprintf('SEED %04d', $index)],
                [
                    'company_id' => $company->id,
                    'fuel_type_id' => $fuelType->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'vehicle_brand_id' => $vehicleBrand->id,
                    'type' => $vehicleType->name,
                    'entry_year' => (string) (2026 - ($index % 8)),
                    'mileage' => (string) ($index * 12500),
                    'status' => $index % 5 === 0 ? 'PASSIVE' : 'ACTIVE',
                    'fuel_type' => $fuelType->name,
                    'mtc' => $index % 3 === 0 ? 'YES' : 'NO',
                    'desc' => 'Seeded vehicle '.$index.' for demo data.',
                    'machine_number' => sprintf('SEED-MCH-%04d', $index),
                    'vin_number' => sprintf('SEED-VIN-%012d', $index),
                    'bpkb_image' => [sprintf('vehicle_documents/seed-%04d-bpkb.jpg', $index)],
                    'stnk_image' => [sprintf('vehicle_documents/seed-%04d-stnk.jpg', $index)],
                    'kir_image' => [sprintf('vehicle_documents/seed-%04d-kir.jpg', $index)],
                    'bpkb_due_date' => now()->addMonths(12 + $index)->toDateString(),
                    'stnk_due_date' => now()->addMonths(6 + $index)->toDateString(),
                    'kir_due_date' => now()->addMonths(3 + $index)->toDateString(),
                    'purchase_price' => 120_000_000 + ($index * 7_500_000),
                    'selling_price' => 90_000_000 + ($index * 5_000_000),
                    'purchase_date' => now()->subYears(1 + ($index % 5))->subMonths($index)->toDateString(),
                    'sale_date' => null,
                ],
            );

            if ($vehicle->trashed()) {
                $vehicle->restore();
            }

            $this->seedPhoto($vehicle, $index);
            $this->seedInsurance($vehicle, $index);
        }
    }

    private function seedPhoto(Vehicle $vehicle, int $index): void
    {
        $photo = VehiclePhoto::withTrashed()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'period' => now()->startOfMonth()->toDateString(),
            ],
            [
                'photos' => collect(VehiclePhoto::SIDES)
                    ->keys()
                    ->mapWithKeys(fn (string $side): array => [
                        $side => sprintf('vehicle_photos/seed-%04d-%s.jpg', $index, $side),
                    ])
                    ->all(),
                'notes' => 'Seeded monthly vehicle photo set.',
            ],
        );

        if ($photo->trashed()) {
            $photo->restore();
        }
    }

    private function seedInsurance(Vehicle $vehicle, int $index): void
    {
        $startDate = now()->subMonths($index);

        $insurance = VehicleInsurance::withTrashed()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'policy_number' => sprintf('SEED-POL-%04d', $index),
            ],
            [
                'insurer_name' => ['Jasindo', 'Astra Buana', 'Sinar Mas', 'Allianz'][($index - 1) % 4],
                'insurance_type' => $index % 2 === 0 ? 'TLO' : 'All Risk',
                'start_date' => $startDate->toDateString(),
                'end_date' => $startDate->copy()->addYear()->toDateString(),
                'coverage_value' => 150_000_000 + ($index * 10_000_000),
                'premium' => 2_500_000 + ($index * 250_000),
                'notes' => 'Seeded vehicle insurance policy.',
                'attachments' => [sprintf('vehicle_insurances/seed-policy-%04d.pdf', $index)],
            ],
        );

        if ($insurance->trashed()) {
            $insurance->restore();
        }
    }
}
