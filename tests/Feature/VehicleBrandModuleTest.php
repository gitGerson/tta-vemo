<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\VehicleBrand;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleBrandModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_vehicle_brand_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithVehicleBrandPermissions());

        $this->get(route('vehicle-brands.index'))
            ->assertOk()
            ->assertSee('Daftar Vehicle Brand');
    }

    public function test_authorized_user_can_create_vehicle_brand_with_logo(): void
    {
        Storage::fake('local');
        Storage::fake('s3');
        $this->actingAs($this->userWithVehicleBrandPermissions());
        Storage::disk('local')->put('tmp/filepond/logo.png', $this->fakeImageContents());

        $this->post(route('vehicle-brands.store'), [
            'code' => 'toy',
            'name' => 'toyota',
            'logo' => 'tmp/filepond/logo.png',
            'descriptions' => 'Japanese vehicle brand.',
        ])->assertRedirect(route('vehicle-brands.index'));

        $vehicleBrand = VehicleBrand::query()->where('code', 'TOY')->firstOrFail();

        $this->assertSame('TOYOTA', $vehicleBrand->name);
        $this->assertStringStartsWith('vehicle_brand_logos/', $vehicleBrand->logo);
        Storage::disk('s3')->assertExists($vehicleBrand->logo);
        $this->assertDatabaseHas('vehicle_brands', [
            'id' => $vehicleBrand->id,
            'code' => 'TOY',
            'name' => 'TOYOTA',
            'descriptions' => 'Japanese vehicle brand.',
        ]);
    }

    public function test_authorized_user_can_update_vehicle_brand_and_keep_existing_logo(): void
    {
        Storage::fake('s3');
        $this->actingAs($this->userWithVehicleBrandPermissions());
        Storage::disk('s3')->put('vehicle_brand_logos/existing.png', 'old');
        $vehicleBrand = VehicleBrand::factory()->create([
            'code' => 'HND',
            'name' => 'HONDA',
            'logo' => 'vehicle_brand_logos/existing.png',
        ]);

        $this->put(route('vehicle-brands.update', $vehicleBrand), [
            'code' => $vehicleBrand->code,
            'name' => 'honda motor',
            'logo' => $vehicleBrand->logoUrl(),
            'descriptions' => 'Updated description.',
        ])->assertRedirect(route('vehicle-brands.index'));

        $this->assertDatabaseHas('vehicle_brands', [
            'id' => $vehicleBrand->id,
            'name' => 'HONDA MOTOR',
            'logo' => 'vehicle_brand_logos/existing.png',
            'descriptions' => 'Updated description.',
        ]);
    }

    public function test_keeping_existing_logo_tolerates_changing_signed_url(): void
    {
        Storage::fake('s3');
        // Pre-signed URLs differ on every generation (signature/expiry query string).
        // Emulate that so the logo URL embedded in the form (page load) differs from
        // the one regenerated on submit.
        Storage::disk('s3')->buildTemporaryUrlsUsing(
            fn (string $path, $expiration, array $options = []): string => 'https://cdn.test/'.$path.'?sig='.uniqid()
        );

        $this->actingAs($this->userWithVehicleBrandPermissions());
        Storage::disk('s3')->put('vehicle_brand_logos/existing.png', 'old');
        $vehicleBrand = VehicleBrand::factory()->create([
            'code' => 'HND',
            'name' => 'HONDA',
            'logo' => 'vehicle_brand_logos/existing.png',
        ]);

        $this->put(route('vehicle-brands.update', $vehicleBrand), [
            'code' => $vehicleBrand->code,
            'name' => 'honda motor',
            'logo' => $vehicleBrand->logoUrl(),
            'descriptions' => 'Updated description.',
        ])->assertRedirect(route('vehicle-brands.index'));

        $this->assertDatabaseHas('vehicle_brands', [
            'id' => $vehicleBrand->id,
            'name' => 'HONDA MOTOR',
            'logo' => 'vehicle_brand_logos/existing.png',
        ]);
    }

    public function test_vehicle_brand_requires_unique_code_and_name(): void
    {
        $this->actingAs($this->userWithVehicleBrandPermissions());
        VehicleBrand::factory()->create([
            'code' => 'TYT',
            'name' => 'TOYOTA',
        ]);

        $this->post(route('vehicle-brands.store'), [
            'code' => 'TYT',
            'name' => 'TOYOTA',
        ])->assertSessionHasErrors(['code', 'name']);
    }

    public function test_authorized_user_can_view_vehicle_brand_detail_fragment(): void
    {
        $this->actingAs($this->userWithVehicleBrandPermissions());
        $vehicleBrand = VehicleBrand::factory()->create([
            'code' => 'TYT',
            'name' => 'TOYOTA',
            'descriptions' => 'Detail description.',
        ]);

        $this->get(route('vehicle-brands.show', $vehicleBrand))
            ->assertOk()
            ->assertSee('TYT')
            ->assertSee('TOYOTA')
            ->assertSee('Detail description.');
    }

    public function test_vehicle_brand_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithVehicleBrandPermissions());
        VehicleBrand::factory()->create([
            'code' => 'DT',
            'name' => 'DATA BRAND',
        ]);

        $this->get(route('vehicle-brands.data'))
            ->assertOk()
            ->assertJsonFragment(['code' => 'DT'])
            ->assertJsonFragment(['name' => 'DATA BRAND'])
            ->assertSee('btn-edit-vehicle-brand', false);
    }

    public function test_authorized_user_can_delete_vehicle_brand(): void
    {
        $this->actingAs($this->userWithVehicleBrandPermissions());
        $vehicleBrand = VehicleBrand::factory()->create();

        $this->delete(route('vehicle-brands.destroy', $vehicleBrand))
            ->assertRedirect(route('vehicle-brands.index'));

        $this->assertSoftDeleted('vehicle_brands', ['id' => $vehicleBrand->id]);
    }

    public function test_vehicle_brand_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithVehicleBrandPermissions();
        $this->actingAs($user);

        // Vehicle Brand lives in the vehicle index "Referensi" dropdown, not the
        // desktop sidebar; it remains reachable via the search palette.
        $menuItems = app(MenuService::class)->itemsForContext('search', $user);

        $this->assertContains('vehicle-brands-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_vehicle_brand_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_brands.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_brands.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_brands.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_brands.delete']);
    }

    private function fakeImageContents(): string
    {
        $file = UploadedFile::fake()->image('logo.png', 20, 20);

        return (string) file_get_contents($file->getRealPath());
    }

    private function userWithVehicleBrandPermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'vehicle-brand-manager',
            'display_name' => 'Vehicle Brand Manager',
        ]);

        collect([
            'master.vehicle_brands.view',
            'master.vehicle_brands.create',
            'master.vehicle_brands.edit',
            'master.vehicle_brands.delete',
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
