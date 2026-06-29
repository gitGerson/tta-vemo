<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
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
            'name' => 'TTA Company',
            'email' => 'tta@example.test',
            'phone' => '021123456',
            'image' => null,
            'notes' => 'Main company',
        ])->toArray();

        $this->post(route('companies.store'), $payload)
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('companies', [
            'name' => 'TTA Company',
            'email' => 'tta@example.test',
            'phone' => '021123456',
            'notes' => 'Main company',
        ]);

        // Code is generated automatically rather than supplied by the user.
        $this->assertSame('CMP-0001', Company::query()->where('name', 'TTA Company')->value('code'));
    }

    public function test_authorized_user_can_update_company(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        $company = Company::factory()->create();

        $payload = Company::factory()->make([
            'code' => $company->code,
            'name' => 'Updated Company',
            'email' => 'updated@example.test',
            'phone' => '021654321',
            'image' => null,
            'notes' => 'Updated description',
        ])->toArray();

        $this->put(route('companies.update', $company), $payload)
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Company',
            'email' => 'updated@example.test',
            'phone' => '021654321',
            'notes' => 'Updated description',
        ]);
    }

    public function test_authorized_user_can_view_company_detail_page(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        $company = Company::factory()->create([
            'code' => 'ABC123',
            'name' => 'Detail Company',
            'email' => 'detail@example.test',
            'phone' => '021000111',
        ]);

        $this->get(route('companies.show', $company))
            ->assertOk()
            ->assertSee('Detail Perusahaan')
            ->assertSee('Kembali')
            ->assertSee('ABC123')
            ->assertSee('Detail Company')
            ->assertSee('detail@example.test')
            ->assertSee('021000111')
            ->assertSee('Data Terkait')
            ->assertSee('Driver')
            ->assertSee('Kendaraan')
            ->assertSee('User')
            ->assertSee('Audit');
    }

    public function test_company_relation_data_endpoints_return_only_related_records(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $driver = Driver::factory()->create(['name' => 'Related Driver']);
        $unrelatedDriver = Driver::factory()->create(['name' => 'Unrelated Driver']);
        $driver->companies()->attach($company);
        $unrelatedDriver->companies()->attach($otherCompany);

        $user = User::factory()->create(['name' => 'Related User']);
        $unrelatedUser = User::factory()->create(['name' => 'Unrelated User']);
        $user->companies()->attach($company);
        $unrelatedUser->companies()->attach($otherCompany);

        Vehicle::factory()->create([
            'company_id' => $company->id,
            'plat_new' => 'REL 100',
        ]);
        Vehicle::factory()->create([
            'company_id' => $otherCompany->id,
            'plat_new' => 'OUT 200',
        ]);

        $this->get(route('companies.drivers.data', ['company' => $company, 'engine' => 'tabulator']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Related Driver')
            ->assertDontSee('Unrelated Driver');

        $this->get(route('companies.users.data', ['company' => $company, 'engine' => 'tabulator']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Related User')
            ->assertDontSee('Unrelated User');

        $this->get(route('companies.vehicles.data', ['company' => $company, 'engine' => 'tabulator']))
            ->assertOk()
            ->assertJsonPath('data.0.plat_new', 'REL 100')
            ->assertDontSee('OUT 200');
    }

    public function test_company_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithCompanyPermissions());
        Company::factory()->create([
            'code' => 'DAT001',
            'name' => 'Data Company',
            'email' => 'data@example.test',
            'phone' => '021222333',
        ]);

        $this->get(route('companies.data'))
            ->assertOk()
            ->assertJsonFragment(['code' => 'DAT001'])
            ->assertJsonFragment(['email' => 'data@example.test'])
            ->assertJsonFragment(['phone' => '021222333'])
            ->assertJsonPath('data.0.row_url', route('companies.show', Company::query()->first()))
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

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
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
            'management.tenant.bypass',
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
