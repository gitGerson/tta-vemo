<?php

namespace Database\Seeders;

use App\Models\{Company, Destination, Driver, FuelType, Maintenance, MaintenanceDetail, Record, TypeMaintenances, User, Vehicle, VehicleBrand, VehicleType};
use App\Services\RecordFuelCostCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Synthetic local-only VEMO dataset. Never imports operational or employee data.
 * Independent of DatabaseSeeder and legacy production-facing seeders.
 *
 * Run after RolePermissionSeeder, FocusedVemoRoleSeeder and FocusedVemoUserSeeder.
 */
class FocusedVemoDemoDataSeeder extends Seeder
{
    public function run(RecordFuelCostCalculator $calculator): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('FocusedVemoDemoDataSeeder only runs in local.');

            return;
        }

        DB::transaction(function () use ($calculator): void {
            $companies = [];
            foreach (['DEMO-A' => 'DEMO Nusantara Logistik', 'DEMO-B' => 'DEMO Sentra Distribusi', 'DEMO-C' => 'DEMO Armada Cabang'] as $code => $name) {
                $companies[] = Company::withTrashed()->updateOrCreate(['code' => $code], [
                    'name' => $name, 'email' => strtolower($code).'@example.test',
                    'phone' => '0210000000', 'notes' => 'SYNTHETIC DEMO - NOT REAL COMPANY DATA',
                ]);
            }

            $brands = [];
            foreach (['TRUCK' => 'DEMO Truck Brand', 'VAN' => 'DEMO Van Brand'] as $key => $name) {
                $brands[$key] = VehicleBrand::withTrashed()->updateOrCreate(['code' => 'DM-BR-'.$key], ['name' => $name, 'descriptions' => 'Synthetic demo']);
            }

            $types = [];
            foreach (['TRUCK' => 'DEMO TRUCK', 'VAN' => 'DEMO VAN'] as $key => $name) {
                $types[$key] = VehicleType::withTrashed()->updateOrCreate(['code' => 'DM-VT-'.$key], ['name' => $name, 'descriptions' => 'Synthetic demo']);
            }

            $fuels = [];
            foreach (['DEMO DIESEL' => ['solar', 12000], 'DEMO GASOLINE' => ['bensin', 14000]] as $name => [$type, $price]) {
                $fuels[$name] = FuelType::withTrashed()->updateOrCreate(['name' => $name, 'merk' => 'Pertamina'], [
                    'type' => $type, 'price' => $price,
                ]);
            }

            $destinations = [];
            foreach (['DEMO Gudang Timur', 'DEMO Hub Barat', 'DEMO Distribusi Tengah'] as $name) {
                $destinations[] = Destination::withTrashed()->updateOrCreate(['name' => $name], ['desc' => 'Synthetic delivery destination']);
            }

            $serviceTypes = [];
            foreach (['DEMO Penggantian Oli' => 'barang', 'DEMO Pemeriksaan Rem' => 'jasa', 'DEMO Penggantian Filter' => 'barang'] as $name => $kind) {
                $serviceTypes[] = TypeMaintenances::withTrashed()->updateOrCreate(['name' => $name], ['jenis' => $kind]);
            }

            $drivers = [];
            foreach (['Andi Contoh', 'Budi Simulasi', 'Citra Uji', 'Dina Demo', 'Eko Latihan', 'Fajar Sampel'] as $i => $name) {
                $driver = Driver::withTrashed()->updateOrCreate(['nik' => sprintf('DM%08d', $i + 1)], [
                    'name' => $name,
                    'address' => 'Alamat sintetis '.$i,
                    'no_identity' => sprintf('990000000000%04d', $i + 1),
                    'residence' => 'Kota Contoh',
                    'date_birth' => '1990-01-01',
                    'no_phone_1' => sprintf('08990000%04d', $i + 1),
                    'no_phone_2' => null,
                    'entry_date' => '2024-01-01',
                    'status' => 'ACTIVE',
                ]);
                if ($driver->trashed()) $driver->restore();
                $driver->companies()->sync([$companies[$i % 3]->id]);
                $drivers[] = $driver;
            }

            // Ensure company-scoped demo users can operate on demo companies.
            foreach (['admin@test.local', 'spv@test.local'] as $email) {
                $user = User::where('email', $email)->first();
                if ($user) $user->companies()->syncWithoutDetaching(array_map(fn ($c) => $c->id, $companies));
            }

            foreach (range(1, 9) as $i) {
                $company = $companies[($i - 1) % 3];
                $key = $i % 3 === 0 ? 'VAN' : 'TRUCK';
                $fuel = $i % 3 === 0 ? $fuels['DEMO GASOLINE'] : $fuels['DEMO DIESEL'];
                $plate = sprintf('DEMO %04d', $i);
                $initialKm = 18000 + $i * 2500;

                $vehicle = Vehicle::withTrashed()->updateOrCreate(['plat_new' => $plate], [
                    'company_id' => $company->id,
                    'vehicle_type_id' => $types[$key]->id,
                    'vehicle_brand_id' => $brands[$key]->id,
                    'fuel_type_id' => $fuel->id,
                    'fuel_type' => $fuel->name,
                    'type' => $types[$key]->name,
                    'entry_year' => (string) (2019 + $i % 6),
                    'mileage' => (string) ($initialKm + 600),
                    'status' => 'ACTIVE',
                    'mtc' => 'NO',
                    'desc' => 'SYNTHETIC DEMO VEHICLE',
                ]);
                if ($vehicle->trashed()) $vehicle->restore();

                foreach (range(1, 3) as $trip) {
                    $start = $initialKm + ($trip - 1) * 200;
                    $finish = $start + 200;
                    $liters = 20 + $trip;
                    $unitPrice = (string) $fuel->price;
                    $date = now()->subDays(20 - $trip * 3)->toDateString();

                    $record = Record::withTrashed()->updateOrCreate(
                        ['vehicle_id' => $vehicle->id, 'date' => $date],
                        [
                            'driver_1' => $drivers[($i + $trip - 2) % 6]->name,
                            'driver_2' => null,
                            'no_pol' => $plate,
                            'destination' => $destinations[($i + $trip - 2) % 3]->name,
                            'company_id' => $company->id,
                            'fuel_type_id' => $fuel->id,
                            'start_km' => (string) $start,
                            'finish_km' => (string) $finish,
                            'totals' => '200',
                            'bbm' => (string) $liters,
                            'fuel_unit_price' => $unitPrice,
                            'idr' => $calculator->calculate($unitPrice, (string) $liters),
                            'scale' => number_format(200 / $liters, 2, '.', ''),
                        ]
                    );
                    if ($record->trashed()) $record->restore();
                }

                // Cases: draft, completed waiting for SPV, and already confirmed.
                $status = $i % 3 === 1 ? Maintenance::STATUS_DRAFT : Maintenance::STATUS_COMPLETED;
                $confirmed = $i % 3 === 0;
                $maintenance = Maintenance::withTrashed()->updateOrCreate(
                    ['code_mt' => sprintf('MT-DEMO-%04d', $i)],
                    [
                        'no_pol' => $plate,
                        'date_in' => now()->subDays(10 + $i)->toDateString(),
                        'date_out' => $status === Maintenance::STATUS_COMPLETED ? now()->subDays(8 + $i)->toDateString() : null,
                        'mileage' => (string) ($initialKm + 400),
                        'description' => 'SYNTHETIC DEMO: routine service/repair scenario',
                        'isServiceRoutine' => $i % 2 === 0,
                        'status' => $status,
                        'requested_by_name' => 'Admin VEMO',
                        'is_confirmed' => $confirmed,
                        'confirmed_at' => $confirmed ? now()->subDays(5) : null,
                        'confirmed_by' => $confirmed ? User::where('email', 'spv@test.local')->value('id') : null,
                        'confirmation_notes' => $confirmed ? 'DEMO: verified by SPV' : null,
                    ]
                );
                if ($maintenance->trashed()) $maintenance->restore();
                // Only replace details belonging to this synthetic maintenance.
                $maintenance->details()->forceDelete();
                foreach ([0 => [1, 125000], 1 => [2, 65000]] as $index => [$qty, $price]) {
                    MaintenanceDetail::create([
                        'maintenance_id' => $maintenance->id,
                        'maintenance_name' => $serviceTypes[$index]->name,
                        'price' => $price,
                        'qty' => (string) $qty,
                        'subtotal' => $qty * $price,
                        'material' => 'Synthetic test item',
                    ]);
                }
                $maintenance->recalculateTotal();
            }
        });

        $this->command?->info('Synthetic VEMO dataset seeded: 3 companies, 6 drivers, 9 vehicles, 27 trips, 9 maintenance cases.');
    }
}
