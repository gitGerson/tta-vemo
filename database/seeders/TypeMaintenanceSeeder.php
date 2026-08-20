<?php

namespace Database\Seeders;

use App\Models\TypeMaintenances;
use Illuminate\Database\Seeder;

class TypeMaintenanceSeeder extends Seeder
{
    /**
     * Seed type maintenances from public/master_type_maintenance.csv.
     * Columns: No, Nama Jenis Perbaikan (name), Jenis (jasa|barang).
     */
    public function run(): void
    {
        $path = public_path('master_type_maintenance.csv');

        if (! is_file($path) || ($handle = fopen($path, 'r')) === false) {
            $this->command?->warn("CSV not found or unreadable: {$path}");

            return;
        }

        $rows = [];
        fgetcsv($handle); // skip header

        while (($data = fgetcsv($handle)) !== false) {
            $name = trim((string) ($data[1] ?? ''));

            if ($name === '') {
                continue;
            }

            $jenis = strtolower(trim((string) ($data[2] ?? 'jasa')));

            if (! in_array($jenis, ['jasa', 'barang'], true)) {
                $jenis = 'jasa';
            }

            // Dedupe by name (the unique column); a later duplicate wins.
            $rows[$name] = ['name' => $name, 'jenis' => $jenis];
        }

        fclose($handle);

        foreach (array_chunk(array_values($rows), 500) as $chunk) {
            TypeMaintenances::upsert($chunk, ['name'], ['jenis']);
        }

        $this->command?->info(count($rows).' type maintenances seeded.');
    }
}
