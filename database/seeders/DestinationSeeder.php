<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            [
                'name' => 'Gudang Pusat',
                'desc' => 'Kawasan Industri Cikarang.',
            ],
            [
                'name' => 'Pelabuhan Tanjung Priok',
                'desc' => 'Pengiriman kontainer ekspor/impor.',
            ],
            [
                'name' => 'Bandara Soekarno-Hatta',
                'desc' => 'Cargo udara domestik.',
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::updateOrCreate(['name' => $destination['name']], $destination);
        }
    }
}
