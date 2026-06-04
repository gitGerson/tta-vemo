<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_non_bypass_user_only_sees_records_from_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        Vehicle::factory()->create(['company_id' => $companyA->id]);
        Vehicle::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($this->userWithCompanies(['master.vehicles.view'], [$companyA]));

        $this->assertSame([$companyA->id], Company::query()->pluck('id')->all());
        $this->assertSame([$companyA->id], Vehicle::query()->pluck('company_id')->all());
    }

    public function test_non_bypass_user_sees_records_from_all_assigned_companies(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $companyC = Company::factory()->create();
        Vehicle::factory()->create(['company_id' => $companyA->id]);
        Vehicle::factory()->create(['company_id' => $companyB->id]);
        Vehicle::factory()->create(['company_id' => $companyC->id]);

        $this->actingAs($this->userWithCompanies(['master.vehicles.view'], [$companyA, $companyB]));

        $this->assertEqualsCanonicalizing([$companyA->id, $companyB->id], Company::query()->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$companyA->id, $companyB->id], Vehicle::query()->pluck('company_id')->all());
    }

    public function test_bypass_user_sees_all_records(): void
    {
        Company::factory()->count(3)->create();

        $this->actingAs($this->userWithCompanies(['master.vehicles.view', 'management.tenant.bypass'], []));

        $this->assertSame(3, Company::query()->count());
    }

    public function test_user_without_company_sees_nothing(): void
    {
        Company::factory()->count(2)->create();

        $this->actingAs($this->userWithCompanies(['master.vehicles.view'], []));

        $this->assertSame(0, Company::query()->count());
    }

    public function test_non_bypass_create_assigns_authenticated_users_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $this->actingAs($this->userWithCompanies(['master.vehicles.create'], [$companyA]));

        $vehicle = Vehicle::factory()->create(['company_id' => $companyB->id]);

        $this->assertSame($companyA->id, $vehicle->fresh()->company_id);
    }

    public function test_non_bypass_user_cannot_open_other_company_vehicle_show(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $vehicleB = Vehicle::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($this->userWithCompanies(['master.vehicles.view'], [$companyA]));

        $this->get(route('vehicles.show', $vehicleB))->assertNotFound();
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWithCompanies(array $permissions, array $companies): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Tenant Test Role',
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
