<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_company_id_is_derived_from_the_vehicle_plate(): void
    {
        $company = Company::factory()->create();
        $maintenance = $this->makeMaintenance($company);

        $this->assertSame($company->id, $maintenance->fresh()->company_id);
    }

    public function test_non_bypass_user_only_sees_own_company_maintenances(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $this->makeMaintenance($companyA);
        $this->makeMaintenance($companyB);

        $this->actingAs($this->userWith(['operational.maintenances.view'], [$companyA]));

        $this->assertSame([$companyA->id], Maintenance::query()->pluck('company_id')->all());
    }

    public function test_bypass_user_sees_maintenances_from_all_companies(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $this->makeMaintenance($companyA);
        $this->makeMaintenance($companyB);

        $this->actingAs($this->userWith(['operational.maintenances.view', 'management.tenant.bypass'], []));

        $this->assertSame(2, Maintenance::query()->count());
    }

    public function test_non_bypass_user_cannot_confirm_other_company_maintenance(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $maintenanceB = $this->makeMaintenance($companyB, Maintenance::STATUS_COMPLETED);

        $this->actingAs($this->userWith(['operational.maintenances.approve'], [$companyA]));

        $this->post(route('maintenances.confirm', $maintenanceB))->assertNotFound();

        $this->assertFalse($maintenanceB->fresh()->is_confirmed);
    }

    public function test_awaiting_confirmation_notification_is_company_scoped(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $awaitingA = $this->makeMaintenance($companyA, Maintenance::STATUS_COMPLETED);
        $awaitingB = $this->makeMaintenance($companyB, Maintenance::STATUS_COMPLETED);

        $this->actingAs($this->userWith(['operational.maintenances.approve'], [$companyA]));

        $titles = collect($this->getJson(route('notifications.index'))->assertOk()->json('items'))
            ->where('type', 'maintenance.awaiting-confirmation')
            ->pluck('title')
            ->all();

        $this->assertContains($awaitingA->no_pol, $titles);
        $this->assertNotContains($awaitingB->no_pol, $titles);
    }

    /**
     * A maintenance whose plate belongs to the given company. company_id is derived
     * from the vehicle by the model's saving hook.
     */
    private function makeMaintenance(Company $company, string $status = Maintenance::STATUS_DRAFT): Maintenance
    {
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);

        return Maintenance::create([
            'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
            'no_pol' => $vehicle->plat_new,
            'status' => $status,
            'is_confirmed' => false,
            'date_in' => now()->toDateString(),
        ]);
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWith(array $permissions, array $companies): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Maintenance Tenant Test Role',
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
