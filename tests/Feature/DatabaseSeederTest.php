<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\FuelType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TypeMaintenances;
use App\Models\User;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DriverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_all_core_seeders_in_sequence(): void
    {
        Http::fake([
            'https://dev-itam.tongtji.com/api/employee' => Http::response([
                'data' => [
                    ['company' => 'Tong Tji'],
                    ['company' => 'Tong Tji Logistics'],
                ],
            ]),
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['username' => 'stagingpurpose']);
        $this->assertGreaterThan(0, Role::count());
        $this->assertGreaterThan(0, Permission::count());
        $this->assertSame(2, Company::count());
        $this->assertSame(7, VehicleBrand::count());
        $this->assertSame(8, VehicleType::count());
        $this->assertGreaterThan(0, FuelType::count());
        $this->assertGreaterThan(0, TypeMaintenances::count());
        $this->assertSame(15, Driver::count());

        $user = User::where('username', 'stagingpurpose')->firstOrFail();

        $this->assertTrue($user->roles()->exists());
    }

    public function test_driver_seeder_can_reset_drivers_with_existing_company_assignments(): void
    {
        $company = Company::factory()->create();
        $driver = Driver::factory()->create();
        $driver->companies()->attach($company);

        $this->assertDatabaseCount('company_driver', 1);

        $this->seed(DriverSeeder::class);

        $this->assertSame(15, Driver::count());
        $this->assertSame(0, DB::table('company_driver')->count());
    }
}
