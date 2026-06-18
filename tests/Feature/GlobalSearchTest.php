<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\MenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_search_redirects_to_login(): void
    {
        $this->get(route('search', ['q' => 'anything']))
            ->assertRedirect(route('login'));
    }

    public function test_search_returns_only_permitted_models(): void
    {
        $driver = Driver::factory()->create(['name' => 'Findme Alpha']);
        Vehicle::factory()->create(['plat_new' => 'FINDME 123']);

        $this->actingAs($this->userWithPermissions([
            'master.drivers.view',
            'management.tenant.bypass',
        ]));

        $response = $this->getJson(route('search', ['q' => 'findme']));

        $response->assertOk()
            ->assertJsonFragment(['group' => 'driver', 'title' => 'Findme Alpha']);

        $groups = collect($response->json('results'))->pluck('group');
        $this->assertTrue($groups->contains('driver'));
        $this->assertFalse($groups->contains('vehicle'));
    }

    public function test_search_respects_tenant_scope(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $driverB = Driver::factory()->create(['name' => 'Tenant Bravo']);
        $driverB->companies()->attach($companyB->id);

        $this->actingAs($this->userWithPermissions(['master.drivers.view'], [$companyA]));

        $results = $this->getJson(route('search', ['q' => 'tenant']))
            ->assertOk()
            ->json('results');

        $this->assertEmpty($results);

        $driverB->companies()->attach($companyA->id);

        $this->getJson(route('search', ['q' => 'tenant']))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Tenant Bravo']);
    }

    public function test_soft_deleted_records_are_excluded(): void
    {
        $driver = Driver::factory()->create(['name' => 'Ghost Charlie']);
        $driver->delete();

        $this->actingAs($this->userWithPermissions([
            'master.drivers.view',
            'management.tenant.bypass',
        ]));

        $this->getJson(route('search', ['q' => 'ghost']))
            ->assertOk()
            ->assertJsonMissing(['title' => 'Ghost Charlie']);
    }

    public function test_query_below_min_length_returns_empty(): void
    {
        Driver::factory()->create(['name' => ' Available']);

        $this->actingAs($this->userWithPermissions([
            'master.drivers.view',
            'management.tenant.bypass',
        ]));

        $this->getJson(route('search', ['q' => 'a']))
            ->assertOk()
            ->assertExactJson(['results' => []]);
    }

    public function test_results_are_capped_per_model(): void
    {
        Driver::factory()->count(8)->create([
            'name' => fn () => 'Capped '.fake()->unique()->lastName(),
        ]);

        $this->actingAs($this->userWithPermissions([
            'master.drivers.view',
            'management.tenant.bypass',
        ]));

        $results = $this->getJson(route('search', ['q' => 'capped']))
            ->assertOk()
            ->json('results');

        $drivers = collect($results)->where('group', 'driver');

        $this->assertSame(config('search.per_model_limit'), $drivers->count());
    }

    public function test_result_url_uses_raw_id_for_driver(): void
    {
        $driver = Driver::factory()->create(['name' => 'Linkable Delta']);

        $this->actingAs($this->userWithPermissions([
            'master.drivers.view',
            'management.tenant.bypass',
        ]));

        $result = collect($this->getJson(route('search', ['q' => 'linkable']))->json('results'))
            ->firstWhere('title', 'Linkable Delta');

        $this->assertNotNull($result);
        $this->assertSame(route('drivers.show', $driver), $result['url']);
        $this->assertStringContainsString('/'.$driver->getKey(), $result['url']);
    }

    public function test_menu_search_includes_create_shortcut_only_for_permitted_user(): void
    {
        $menu = app(MenuService::class);

        $permitted = $this->userWithPermissions(['master.vehicles.create']);
        $keys = collect($menu->itemsForContext('search', $permitted))->pluck('key');
        $this->assertTrue($keys->contains('vehicles-create'));

        $unpermitted = $this->userWithPermissions(['master.drivers.view']);
        $keys = collect($menu->itemsForContext('search', $unpermitted))->pluck('key');
        $this->assertFalse($keys->contains('vehicles-create'));
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWithPermissions(array $permissions, array $companies = []): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Search Test Role',
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
