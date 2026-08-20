<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Note;
use App\Models\TypeMaintenances;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::query()
            ->where('plat_new', 'like', 'SEED %')
            ->orderBy('plat_new')
            ->get();
        $types = TypeMaintenances::query()->orderBy('id')->limit(20)->get();

        if ($vehicles->isEmpty() || $types->isEmpty()) {
            $this->command?->warn('MaintenanceSeeder skipped. Seed vehicles and type maintenances first.');

            return;
        }

        for ($index = 1; $index <= 10; $index++) {
            $vehicle = $vehicles[($index - 1) % $vehicles->count()];
            $dateIn = now()->subDays(30 - $index);
            $status = $index % 4 === 0 ? Maintenance::STATUS_DRAFT : Maintenance::STATUS_COMPLETED;

            $maintenance = Maintenance::withTrashed()->updateOrCreate(
                ['code_mt' => sprintf('MT-SEED-%04d', $index)],
                [
                    'no_pol' => $vehicle->plat_new,
                    'total_amount' => 0,
                    'date_in' => $dateIn->toDateString(),
                    'date_check' => $dateIn->copy()->addDay()->toDateString(),
                    'date_out' => $status === Maintenance::STATUS_COMPLETED ? $dateIn->copy()->addDays(2)->toDateString() : null,
                    'mileage' => (string) ((int) $vehicle->mileage + ($index * 250)),
                    'description' => 'Seeded maintenance for '.$vehicle->plat_new.'.',
                    'isServiceRoutine' => $index % 2 === 1,
                    'status' => $status,
                    'viewed_at' => $status === Maintenance::STATUS_COMPLETED ? now()->subDays($index) : null,
                    'viewed_by' => null,
                    'is_confirmed' => $status === Maintenance::STATUS_COMPLETED && $index % 3 === 0,
                    'confirmed_at' => $status === Maintenance::STATUS_COMPLETED && $index % 3 === 0 ? now()->subDays($index - 1) : null,
                    'confirmed_by' => null,
                    'confirmation_notes' => $status === Maintenance::STATUS_COMPLETED && $index % 3 === 0 ? 'Seeded confirmation note.' : null,
                    'requested_by_name' => 'Seeder',
                ],
            );

            if ($maintenance->trashed()) {
                $maintenance->restore();
            }

            $this->syncDetails($maintenance, $types, $index);
            $this->syncNote($maintenance, $index);
            $maintenance->recalculateTotal();
        }
    }

    /**
     * @param  Collection<int, TypeMaintenances>  $types
     */
    private function syncDetails(Maintenance $maintenance, Collection $types, int $index): void
    {
        $maintenance->details()->forceDelete();

        for ($line = 1; $line <= 2; $line++) {
            $type = $types[(($index + $line) - 2) % $types->count()];
            $price = 150_000 + ($index * 25_000) + ($line * 50_000);
            $qty = (string) $line;

            MaintenanceDetail::create([
                'maintenance_id' => $maintenance->id,
                'maintenance_name' => $type->name,
                'price' => $price,
                'qty' => $qty,
                'subtotal' => $price * (int) $qty,
                'material' => $type->jenis === 'barang' ? 'Seeded spare part material.' : null,
            ]);
        }
    }

    private function syncNote(Maintenance $maintenance, int $index): void
    {
        Note::updateOrCreate(
            [
                'maintenances_id' => $maintenance->id,
                'image' => sprintf('maintenances/seed-%04d.jpg', $index),
            ],
            ['url' => null],
        );
    }
}
