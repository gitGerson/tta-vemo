<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\JPayrollService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_driver_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithDriverPermissions());

        $this->get(route('drivers.index'))
            ->assertOk()
            ->assertSee('Daftar Driver');
    }

    public function test_authorized_user_can_create_driver(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $this->mockValidNik('TTI0001938');

        $company = Company::factory()->create();

        $payload = Driver::factory()->make([
            'nik' => 'TTI0001938',
            'no_identity' => '1234567890123456',
            'no_phone_1' => '081234567890',
            'no_phone_2' => '089876543210',
        ])->toArray();
        $payload['company_ids'] = [$company->id];

        $this->post(route('drivers.store'), $payload)
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'name' => $payload['name'],
            'no_identity' => '1234567890123456',
        ]);
        $this->assertDatabaseHas('company_driver', [
            'company_id' => $company->id,
        ]);
    }

    public function test_authorized_user_can_create_driver_without_second_phone_number(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $this->mockValidNik('TTI0001939');

        $payload = Driver::factory()->make([
            'nik' => 'TTI0001939',
            'no_identity' => '1234567890123456',
            'no_phone_1' => '081234567890',
            'no_phone_2' => null,
        ])->toArray();

        $this->post(route('drivers.store'), $payload)
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'name' => $payload['name'],
            'no_phone_2' => null,
        ]);
    }

    public function test_authorized_user_can_create_driver_with_source_company_from_payroll(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $this->mockValidNik('TTI0001941');

        $payload = Driver::factory()->make([
            'nik' => 'TTI0001941',
            'no_identity' => '1234567890123456',
            'no_phone_1' => '081234567890',
            'no_phone_2' => null,
        ])->toArray();
        $payload['source_company_code'] = 'TTA';
        $payload['source_company_name'] = 'PT TTA Source';

        $this->post(route('drivers.store'), $payload)
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('companies', [
            'code' => 'TTA',
            'name' => 'PT TTA Source',
        ]);
        $this->assertDatabaseHas('company_driver', [
            'company_id' => Company::query()->where('code', 'TTA')->value('id'),
        ]);
    }

    public function test_employee_info_response_includes_local_company_match(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $company = Company::factory()->create([
            'code' => 'TTA',
            'name' => 'PT TTA Source',
        ]);

        $this->mock(JPayrollService::class, function ($mock): void {
            $mock->shouldReceive('fetchEmployeeByNik')
                ->once()
                ->with('TTI0001942')
                ->andReturn([
                    'success' => true,
                    'data' => [[
                        'NIK' => 'TTI0001942',
                        'Name' => 'Payroll Driver',
                        'CompanyCode' => 'TTA',
                        'CompanyName' => 'PT TTA Source',
                    ]],
                ]);
        });

        $this->getJson(route('drivers.empInfo', ['nik' => 'TTI0001942']))
            ->assertOk()
            ->assertJsonPath('data.0._company.id', $company->id)
            ->assertJsonPath('data.0._company.code', 'TTA')
            ->assertJsonPath('data.0._company.name', 'PT TTA Source')
            ->assertJsonPath('data.0._company.exists', true);
    }

    public function test_authorized_user_cannot_create_driver_without_nik(): void
    {
        $this->actingAs($this->userWithDriverPermissions());

        $payload = Driver::factory()->make([
            'nik' => null,
            'no_identity' => '1234567890123456',
            'no_phone_1' => '081234567890',
        ])->toArray();

        $this->post(route('drivers.store'), $payload)
            ->assertInvalid(['nik']);
    }

    public function test_authorized_user_can_update_driver(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $driver = Driver::factory()->create(['nik' => 'TTI0001940']);
        $company = Company::factory()->create();

        $payload = Driver::factory()->make([
            'nik' => $driver->nik,
            'name' => 'Updated Driver',
            'no_identity' => $driver->no_identity,
            'no_phone_1' => $driver->no_phone_1,
            'no_phone_2' => $driver->no_phone_2,
        ])->toArray();
        $payload['company_ids'] = [$company->id];

        $this->put(route('drivers.update', $driver), $payload)
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'name' => 'Updated Driver',
        ]);
        $this->assertDatabaseHas('company_driver', [
            'company_id' => $company->id,
            'driver_id' => $driver->id,
        ]);
    }

    public function test_authorized_user_can_view_driver_detail_fragment(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $company = Company::factory()->create([
            'code' => 'TTA',
            'name' => 'PT TTA Source',
        ]);
        $driver = Driver::factory()->create([
            'nik' => 'TTI0001943',
            'name' => 'Detail Driver',
            'no_phone_2' => null,
            'status' => 'ACTIVE',
        ]);
        $driver->companies()->attach($company);

        $this->get(route('drivers.show', $driver))
            ->assertOk()
            ->assertSee('Detail Driver')
            ->assertSee('TTI0001943')
            ->assertSee('PT TTA Source')
            ->assertSee('driver-employee-card', false)
            ->assertSee('Identitas')
            ->assertSee('Kontak & Pekerjaan', false)
            ->assertDontSee('<table', false);
    }

    public function test_authorized_user_can_soft_delete_and_restore_driver(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $driver = Driver::factory()->create();

        $this->delete(route('drivers.destroy', $driver))
            ->assertRedirect(route('drivers.index'));

        $this->assertSoftDeleted('drivers', ['id' => $driver->id]);

        $this->post(route('drivers.restore', $driver))
            ->assertRedirect(route('drivers.trash'));

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'deleted_at' => null,
        ]);
    }

    private function userWithDriverPermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'driver-manager',
            'display_name' => 'Driver Manager',
        ]);

        collect([
            'master.drivers.view',
            'master.drivers.create',
            'master.drivers.edit',
            'master.drivers.delete',
            'master.drivers.restore',
            'master.drivers.force_delete',
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

    private function mockValidNik(string $nik): void
    {
        $this->mock(JPayrollService::class, function ($mock) use ($nik): void {
            $mock->shouldReceive('validateNik')
                ->once()
                ->with($nik)
                ->andReturnTrue();
        });
    }
}
