<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('company_driver')->delete();
        DB::table((new Driver)->getTable())->delete();

        Driver::factory()->withRandomImage()->count(15)->create();
    }
}
