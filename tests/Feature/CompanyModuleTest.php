<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_company_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());

        $this->get(route('companies.index'))
            ->assertOk()
            ->assertSee('Daftar Perusahaan');
    }

    public function test_authorized_user_can_create_company(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());

        $payload = Company::factory()->make([
            'code' => 'TTA001',
            'name' => 'TTA Company',
            'desc' => 'Main company',
        ])->toArray();

        $this->post(route('companies.store'), $payload)
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('company', [
            'code' => 'TTA001',
            'name' => 'TTA Company',
            'desc' => 'Main company',
        ]);
    }

    public function test_authorized_user_can_update_company(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        $company = Company::factory()->create();

        $payload = Company::factory()->make([
            'code' => $company->code,
            'name' => 'Updated Company',
            'desc' => 'Updated description',
        ])->toArray();

        $this->put(route('companies.update', $company), $payload)
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('company', [
            'id' => $company->id,
            'name' => 'Updated Company',
            'desc' => 'Updated description',
        ]);
    }

    public function test_authorized_user_can_view_company_detail_fragment(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        $company = Company::factory()->create([
            'code' => 'ABC123',
            'name' => 'Detail Company',
        ]);

        $this->get(route('companies.show', $company))
            ->assertOk()
            ->assertSee('ABC123')
            ->assertSee('Detail Company');
    }

    public function test_company_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        Company::factory()->create([
            'code' => 'DAT001',
            'name' => 'Data Company',
        ]);

        $this->get(route('companies.data'))
            ->assertOk()
            ->assertJsonFragment(['code' => 'DAT001'])
            ->assertSee('btn-edit-company', false);
    }

    public function test_company_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithCompanyPermissions();
        $this->actingAs($user);

        $menuItems = app(MenuService::class)->itemsForContext('desktop', $user);

        $this->assertContains('companies-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_company_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.companies.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.companies.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.companies.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.companies.delete']);
    }

    public function test_authorized_user_can_delete_company(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        $company = Company::factory()->create();

        $this->delete(route('companies.destroy', $company))
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseMissing('company', ['id' => $company->id]);
    }

    private function userWithCompanyPermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'company-manager',
            'display_name' => 'Company Manager',
        ]);

        collect([
            'master.companies.view',
            'master.companies.create',
            'master.companies.edit',
            'master.companies.delete',
        ])->each(function (string $name) use ($role): void {
            $permission = Permission::create([
                'name' => $name,
                'display_name' => $name,
                'module' => 'master',
            ]);

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
