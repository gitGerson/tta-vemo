<?php

namespace Tests\Feature;

use App\Models\{Company, Driver, Maintenance, Record, Vehicle};
use Database\Seeders\FocusedVemoDemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FocusedVemoDemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_demo_data_is_synthetic_and_repeatable(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(FocusedVemoDemoDataSeeder::class);

        $this->assertSame(3, Company::where('code', 'like', 'DEMO-%')->count());
        $this->assertSame(6, Driver::where('nik', 'like', 'DM%')->count());
        $this->assertSame(9, Vehicle::where('plat_new', 'like', 'DEMO %')->count());
        $this->assertSame(27, Record::where('no_pol', 'like', 'DEMO %')->count());
        $this->assertSame(9, Maintenance::where('code_mt', 'like', 'MT-DEMO-%')->count());
        $this->assertSame(3, Maintenance::where('code_mt', 'like', 'MT-DEMO-%')->where('status', 'DRAFT')->count());
        $this->assertSame(3, Maintenance::where('code_mt', 'like', 'MT-DEMO-%')->where('status', 'COMPLETED')->where('is_confirmed', false)->count());
        $this->assertSame(3, Maintenance::where('code_mt', 'like', 'MT-DEMO-%')->where('is_confirmed', true)->count());

        $this->seed(FocusedVemoDemoDataSeeder::class);

        $this->assertSame(27, Record::where('no_pol', 'like', 'DEMO %')->count());
        $this->assertSame(9, Maintenance::where('code_mt', 'like', 'MT-DEMO-%')->count());
    }

    public function test_does_not_write_outside_local(): void
    {
        $this->app->instance('env', 'testing');
        $this->seed(FocusedVemoDemoDataSeeder::class);
        $this->assertSame(0, Vehicle::where('plat_new', 'like', 'DEMO %')->count());
    }
}
