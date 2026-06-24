<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleInsuranceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithInsurancePermissions());

        $this->get(route('vehicle-insurances.index'))
            ->assertOk()
            ->assertSee('Daftar Asuransi Kendaraan');
    }

    public function test_index_forbidden_for_unauthorized_user(): void
    {
        $this->actingAs(User::factory()->create());

        // CheckPermission redirects back with an error for web (non-JSON) requests.
        $this->get(route('vehicle-insurances.index'))->assertRedirect();
    }

    public function test_authorized_user_can_create_insurance_and_snapshot_is_recorded(): void
    {
        $this->actingAs($this->userWithInsurancePermissions());

        $vehicle = Vehicle::factory()->create();
        $payload = VehicleInsurance::factory()->make(['vehicle_id' => $vehicle->id])->toArray();

        $this->post(route('vehicle-insurances.store'), $payload)
            ->assertRedirect(route('vehicle-insurances.index'));

        $this->assertDatabaseHas('vehicle_insurances', [
            'vehicle_id' => $vehicle->id,
            'policy_number' => $payload['policy_number'],
        ]);

        $insurance = VehicleInsurance::where('vehicle_id', $vehicle->id)->firstOrFail();

        // Code is generated automatically rather than supplied by the user.
        $this->assertSame('INS-0001', $insurance->code);
        $this->assertDatabaseHas('vehicle_insurance_snapshots', [
            'vehicle_insurance_id' => $insurance->id,
            'vehicle_plat' => $vehicle->plat_new,
        ]);
        $this->assertSame(1, $insurance->snapshots()->count());
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        $this->actingAs($this->userWithInsurancePermissions());

        $vehicle = Vehicle::factory()->create();
        $payload = VehicleInsurance::factory()->make([
            'vehicle_id' => $vehicle->id,
            'start_date' => '2026-01-10',
            'end_date' => '2026-01-01',
        ])->toArray();

        $this->post(route('vehicle-insurances.store'), $payload)
            ->assertSessionHasErrors('end_date');
    }

    public function test_authorized_user_can_update_insurance_and_snapshot_is_recorded(): void
    {
        $this->actingAs($this->userWithInsurancePermissions());

        $insurance = VehicleInsurance::factory()->create();
        $payload = array_merge($insurance->toArray(), [
            'vehicle_id' => $insurance->vehicle_id,
            'insurer_name' => 'Updated Insurer',
            'start_date' => $insurance->start_date->format('Y-m-d'),
            'end_date' => $insurance->end_date->format('Y-m-d'),
        ]);

        $this->put(route('vehicle-insurances.update', $insurance), $payload)
            ->assertRedirect(route('vehicle-insurances.index'));

        $this->assertDatabaseHas('vehicle_insurances', [
            'id' => $insurance->id,
            'insurer_name' => 'Updated Insurer',
        ]);
        // One snapshot from create + one from update.
        $this->assertSame(2, $insurance->snapshots()->count());
    }

    public function test_authorized_user_can_soft_delete_and_restore_insurance(): void
    {
        $this->actingAs($this->userWithInsurancePermissions());

        $insurance = VehicleInsurance::factory()->create();

        $this->delete(route('vehicle-insurances.destroy', $insurance))
            ->assertRedirect(route('vehicle-insurances.index'));
        $this->assertSoftDeleted($insurance);

        $this->post(route('vehicle-insurances.restore', $insurance))
            ->assertRedirect(route('vehicle-insurances.trash'));
        $this->assertDatabaseHas('vehicle_insurances', [
            'id' => $insurance->id,
            'deleted_at' => null,
        ]);
    }

    public function test_field_without_permission_is_ignored_on_create(): void
    {
        // User can create but lacks the "premium" field permission.
        $fields = array_values(array_diff(VehicleInsurance::FORM_FIELDS, ['premium']));
        $this->actingAs($this->userWithInsurancePermissions($fields));

        $vehicle = Vehicle::factory()->create();
        $payload = VehicleInsurance::factory()->make([
            'vehicle_id' => $vehicle->id,
            'premium' => 1234.56,
        ])->toArray();

        $this->post(route('vehicle-insurances.store'), $payload)
            ->assertRedirect(route('vehicle-insurances.index'));

        $insurance = VehicleInsurance::where('vehicle_id', $vehicle->id)->firstOrFail();
        $this->assertNull($insurance->premium);
    }

    public function test_field_without_permission_is_left_untouched_on_update(): void
    {
        $insurance = VehicleInsurance::factory()->create(['insurer_name' => 'Original Insurer']);

        // User can edit but lacks the "insurer_name" field permission.
        $fields = array_values(array_diff(VehicleInsurance::FORM_FIELDS, ['insurer_name']));
        $this->actingAs($this->userWithInsurancePermissions($fields));

        $payload = array_merge($insurance->toArray(), [
            'vehicle_id' => $insurance->vehicle_id,
            'insurer_name' => 'Hacked Insurer',
            'start_date' => $insurance->start_date->format('Y-m-d'),
            'end_date' => $insurance->end_date->format('Y-m-d'),
        ]);

        $this->put(route('vehicle-insurances.update', $insurance), $payload)
            ->assertRedirect(route('vehicle-insurances.index'));

        $this->assertDatabaseHas('vehicle_insurances', [
            'id' => $insurance->id,
            'insurer_name' => 'Original Insurer',
        ]);
    }

    /**
     * @param  array<int, string>|null  $fields  Form fields to grant; null grants all.
     */
    private function userWithInsurancePermissions(?array $fields = null): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'insurance-manager-'.uniqid(),
            'display_name' => 'Insurance Manager',
        ]);

        $fieldPermissions = collect($fields ?? VehicleInsurance::FORM_FIELDS)
            ->map(fn (string $field): string => VehicleInsurance::fieldPermission($field));

        collect([
            'master.vehicle_insurances.view',
            'master.vehicle_insurances.create',
            'master.vehicle_insurances.edit',
            'master.vehicle_insurances.delete',
            'master.vehicle_insurances.restore',
            'master.vehicle_insurances.force_delete',
            'management.tenant.bypass',
        ])->merge($fieldPermissions)->each(function (string $name) use ($role): void {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $name, 'module' => 'master'],
            );

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
