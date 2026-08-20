<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\NotificationService;
use App\Services\VehiclePhotoMonitorService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclePhotoModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_authorized_user_can_create_photo_for_current_month(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $vehicle = Vehicle::factory()->create();

        $this->post(route('vehicle-photos.store'), [
            'vehicle_id' => $vehicle->id,
            'period' => now()->format('Y-m'),
            'notes' => 'Kondisi baik',
        ])->assertRedirect(route('vehicles.show', $vehicle->id));

        $this->assertDatabaseHas('vehicle_photos', [
            'vehicle_id' => $vehicle->id,
            'period' => now()->startOfMonth()->toDateTimeString(),
            'notes' => 'Kondisi baik',
        ]);
    }

    public function test_authorized_user_can_create_photo_without_notes(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $vehicle = Vehicle::factory()->create();

        $this->post(route('vehicle-photos.store'), [
            'vehicle_id' => $vehicle->id,
            'period' => now()->format('Y-m'),
        ])->assertRedirect(route('vehicles.show', $vehicle));

        $this->assertDatabaseHas('vehicle_photos', [
            'vehicle_id' => $vehicle->id,
            'notes' => null,
        ]);
    }

    public function test_second_photo_for_same_vehicle_and_month_is_rejected(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $vehicle = Vehicle::factory()->create();
        VehiclePhoto::factory()->currentMonth()->create(['vehicle_id' => $vehicle->id]);

        $this->post(route('vehicle-photos.store'), [
            'vehicle_id' => $vehicle->id,
            'period' => now()->format('Y-m'),
            'notes' => 'foto kendaraan lengkap',
        ])->assertSessionHasErrors('period');

        $this->assertSame(1, VehiclePhoto::where('vehicle_id', $vehicle->id)->count());
    }

    public function test_authorized_user_can_update_photo_notes(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $photo = VehiclePhoto::factory()->currentMonth()->create();

        $this->put(route('vehicle-photos.update', $photo), [
            'vehicle_id' => $photo->vehicle_id,
            'period' => $photo->period->format('Y-m'),
            'notes' => 'Diperbarui',
        ])->assertRedirect(route('vehicles.show', $photo->vehicle_id));

        $this->assertDatabaseHas('vehicle_photos', [
            'id' => $photo->id,
            'notes' => 'Diperbarui',
        ]);
    }

    public function test_authorized_user_can_clear_photo_notes(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $photo = VehiclePhoto::factory()->currentMonth()->create([
            'notes' => 'Kondisi baik',
        ]);

        $this->put(route('vehicle-photos.update', $photo), [
            'vehicle_id' => $photo->vehicle_id,
            'period' => $photo->period->format('Y-m'),
        ])->assertRedirect(route('vehicles.show', $photo->vehicle));

        $this->assertDatabaseHas('vehicle_photos', [
            'id' => $photo->id,
            'notes' => null,
        ]);
    }

    public function test_authorized_user_can_soft_delete_photo(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $photo = VehiclePhoto::factory()->create();

        $this->delete(route('vehicle-photos.destroy', $photo))
            ->assertRedirect(route('vehicles.show', $photo->vehicle_id));

        $this->assertSoftDeleted($photo);
    }

    public function test_dedicated_upload_page_renders_without_a_preselected_vehicle(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        Vehicle::factory()->create();

        $this->get(route('vehicle-photos.create'))
            ->assertOk()
            ->assertSee('Upload Foto Kendaraan');
    }

    public function test_photo_only_user_without_vehicle_view_is_redirected_to_upload_page(): void
    {
        $user = $this->userWithPhotoPermissions(includeVehicleView: false);
        $this->actingAs($user);

        $vehicle = Vehicle::factory()->create();

        $this->post(route('vehicle-photos.store'), [
            'vehicle_id' => $vehicle->id,
            'period' => now()->format('Y-m'),
            'notes' => 'foto kendaraan lengkap',
        ])->assertRedirect(route('vehicle-photos.create'));
    }

    public function test_standalone_page_feeds_documented_months_to_the_picker(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $vehicle = Vehicle::factory()->create();
        VehiclePhoto::factory()->currentMonth()->create(['vehicle_id' => $vehicle->id]);

        $this->get(route('vehicle-photos.create'))
            ->assertOk()
            ->assertSee('data-period-select', escape: false)
            ->assertSee('periods-by-vehicle', escape: false)
            // The reactive script (pushed to the scripts stack) must be on the page.
            ->assertSee("on('change', '#vehicle_id'", escape: false)
            // The month map exposes this vehicle's documented current month to the JS.
            ->assertSee('"'.$vehicle->id.'":["'.now()->format('Y-m').'"]', escape: false);
    }

    public function test_create_forbidden_for_unauthorized_user(): void
    {
        $this->actingAs(User::factory()->create());

        $vehicle = Vehicle::factory()->create();

        $this->get(route('vehicle-photos.create', ['vehicle_id' => $vehicle->id]))->assertRedirect();
    }

    public function test_monitor_lists_active_vehicle_missing_current_month_and_clears_once_documented(): void
    {
        $user = $this->userWithPhotoPermissions();
        $this->actingAs($user);

        $vehicle = Vehicle::factory()->create(['status' => 'ACTIVE']);

        $monitor = app(VehiclePhotoMonitorService::class);
        $this->assertTrue($monitor->incompleteVehicles()->contains('vehicle_id', $vehicle->id));

        // Surfaced as a navbar notification for a user with the reminders permission.
        $items = collect(app(NotificationService::class)->forUser($user)['items']);
        $this->assertTrue($items->contains('type', 'vehicle.photo-missing'));

        VehiclePhoto::factory()->currentMonth()->create(['vehicle_id' => $vehicle->id]);

        $this->assertFalse($monitor->incompleteVehicles()->contains('vehicle_id', $vehicle->id));
    }

    public function test_partial_current_month_record_still_flags_vehicle_until_all_sides_present(): void
    {
        $this->actingAs($this->userWithPhotoPermissions());

        $vehicle = Vehicle::factory()->create(['status' => 'ACTIVE']);
        $photo = VehiclePhoto::factory()->currentMonth()->partial(2)->create(['vehicle_id' => $vehicle->id]);

        $monitor = app(VehiclePhotoMonitorService::class);
        $flagged = $monitor->incompleteVehicles()->firstWhere('vehicle_id', $vehicle->id);
        $this->assertNotNull($flagged);
        $this->assertSame(2, $flagged['completed']);

        // Completing all four sides clears the flag.
        $photo->update([
            'photos' => collect(VehiclePhoto::SIDES)->keys()
                ->mapWithKeys(fn (string $side): array => [$side => 'vehicle_photos/'.$side.'.jpg'])
                ->all(),
        ]);

        $this->assertFalse($monitor->incompleteVehicles()->contains('vehicle_id', $vehicle->id));
    }

    public function test_completeness_helpers_count_only_known_sides(): void
    {
        $complete = new VehiclePhoto(['photos' => ['front' => 'a', 'rear' => 'b', 'left' => 'c', 'right' => 'd']]);
        $this->assertSame(4, $complete->completedCount());
        $this->assertTrue($complete->isComplete());

        $partial = new VehiclePhoto(['photos' => ['front' => 'a', 'left' => 'c']]);
        $this->assertSame(2, $partial->completedCount());
        $this->assertFalse($partial->isComplete());
    }

    private function userWithPhotoPermissions(bool $includeVehicleView = true): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'photo-manager-'.uniqid(),
            'display_name' => 'Photo Manager',
        ]);

        collect([
            ...($includeVehicleView ? ['master.vehicles.view'] : []),
            'operational.vehicle_photos.view',
            'operational.vehicle_photos.create',
            'operational.vehicle_photos.edit',
            'operational.vehicle_photos.delete',
            'operational.vehicle_photos.reminders',
            'management.tenant.bypass',
        ])->each(function (string $name) use ($role): void {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $name, 'module' => 'operational'],
            );

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
