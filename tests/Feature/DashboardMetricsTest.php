<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Record;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_summary_sums_current_month_maintenance_and_fuel(): void
    {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id, 'status' => 'ACTIVE']);

        Maintenance::create([
            'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
            'no_pol' => $vehicle->plat_new,
            'status' => Maintenance::STATUS_COMPLETED,
            'total_amount' => 750000,
            'isServiceRoutine' => true,
            'mileage' => '10000',
            'date_in' => now()->startOfMonth()->toDateString(),
            'date_out' => now()->startOfMonth()->toDateString(),
        ]);

        // Cancelled maintenance must be excluded from the spend totals.
        Maintenance::create([
            'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
            'no_pol' => $vehicle->plat_new,
            'status' => Maintenance::STATUS_CANCELLED,
            'total_amount' => 999999,
            'isServiceRoutine' => false,
            'mileage' => '11000',
            'date_in' => now()->startOfMonth()->toDateString(),
            'date_out' => now()->startOfMonth()->toDateString(),
        ]);

        Record::factory()->create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicle->id,
            'no_pol' => $vehicle->plat_new,
            'idr' => '250000',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->userWith(['management.tenant.bypass']));

        $summary = app(DashboardMetricsService::class)->financialSummary();

        $this->assertSame(750000.0, $summary['maintenance']['value']);
        $this->assertSame(250000.0, $summary['fuel']['value']);
    }

    public function test_financial_summary_is_company_scoped(): void
    {
        $own = Company::factory()->create();
        $other = Company::factory()->create();

        $ownVehicle = Vehicle::factory()->create(['company_id' => $own->id]);
        $otherVehicle = Vehicle::factory()->create(['company_id' => $other->id]);

        foreach ([$ownVehicle, $otherVehicle] as $vehicle) {
            Maintenance::create([
                'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
                'no_pol' => $vehicle->plat_new,
                'status' => Maintenance::STATUS_COMPLETED,
                'total_amount' => 100000,
                'isServiceRoutine' => true,
                'mileage' => '10000',
                'date_in' => now()->startOfMonth()->toDateString(),
                'date_out' => now()->startOfMonth()->toDateString(),
            ]);
        }

        $this->actingAs($this->userWith(['dashboard.financials.view'], [$own]));

        $summary = app(DashboardMetricsService::class)->financialSummary();

        $this->assertSame(100000.0, $summary['maintenance']['value']);
    }

    public function test_financial_widgets_hidden_without_permission(): void
    {
        $this->actingAs($this->userWith([]));

        $this->get(route('home'))->assertOk()->assertDontSee('Tren Biaya');
    }

    public function test_financial_widgets_visible_with_permission(): void
    {
        $this->actingAs($this->userWith(['dashboard.financials.view']));

        $this->get(route('home'))->assertOk()->assertSee('Tren Biaya');
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWith(array $permissions, array $companies = []): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Dashboard Test Role',
        ]);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $name, 'module' => str($name)->before('.')->value()],
            );

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user->roles()->attach($role->id, ['assigned_at' => now()]);

        if ($companies !== []) {
            $user->companies()->sync(collect($companies)->map(fn (Company $company): int => $company->id)->all());
        }

        $user->load('roles.permissions');

        return $user;
    }
}
