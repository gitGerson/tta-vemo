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
                'destination' => 'Gudang Pusat',
                'desc' => 'Kawasan Industri Cikarang.',
            ],
            [
                'destination' => 'Pelabuhan Tanjung Priok',
                'desc' => 'Pengiriman kontainer ekspor/impor.',
            ],
            [
                'destination' => 'Bandara Soekarno-Hatta',
                'desc' => 'Cargo udara domestik.',
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::updateOrCreate(['destination' => $destination['destination']], $destination);
        }
    }
}
