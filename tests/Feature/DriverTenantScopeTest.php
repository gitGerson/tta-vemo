<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_is_only_visible_for_a_linked_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $driver = Driver::factory()->create();
        $driver->companies()->attach($companyB->id);

        $this->actingAs($this->userWithCompanies(['master.drivers.view'], [$companyA]));

        $this->assertSame(0, Driver::query()->count());

        $driver->companies()->attach($companyA->id);

        $this->assertSame(1, Driver::query()->count());
    }

    public function test_bypass_user_sees_drivers_from_any_company(): void
    {
        $companyB = Company::factory()->create();
        $driver = Driver::factory()->create();
        $driver->companies()->attach($companyB->id);

        $this->actingAs($this->userWithCompanies(['master.drivers.view', 'management.tenant.bypass'], []));

        $this->assertSame(1, Driver::query()->count());
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
