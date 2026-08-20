<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FuelType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_vehicle_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());

        $this->get(route('vehicles.index'))
            ->assertOk()
            ->assertSee('Daftar Kendaraan');
    }

    public function test_create_and_edit_pages_render(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create();

        $this->get(route('vehicles.create'))->assertOk()->assertSee('Tambah Kendaraan');
        $this->get(route('vehicles.edit', $vehicle))->assertOk()->assertSee('Ubah Kendaraan');
    }

    public function test_vehicle_form_marks_type_and_brand_as_required(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());

        $content = (string) $this->get(route('vehicles.create'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<select(?=[^>]*name="vehicle_type_id")(?=[^>]*\brequired\b)[^>]*>/', $content);
        $this->assertMatchesRegularExpression('/<select(?=[^>]*name="vehicle_brand_id")(?=[^>]*\brequired\b)[^>]*>/', $content);
        $this->assertStringNotContainsString('name="isprivate"', $content);
    }

    public function test_isprivate_columns_are_removed(): void
    {
        $this->assertFalse(Schema::hasColumn('vehicles', 'isprivate'));
        $this->assertFalse(Schema::hasColumn('vehicle_snapshots', 'isprivate'));
    }

    public function test_authorized_user_can_create_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $company = Company::factory()->create();

        $payload = $this->validVehiclePayload([
            'company_id' => $company->id,
            'type' => 'Box',
            'entry_year' => '2026',
            'plat_new' => 'H 1234 TT',
            'fuel_type' => 'BBM',
        ]);

        $this->post(route('vehicles.store'), $payload)
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'company_id' => $company->id,
            'type' => 'Box',
            'plat_new' => 'H 1234 TT',
            'fuel_type' => 'BBM',
        ]);
    }

    public function test_vehicle_type_and_brand_are_required_when_creating_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());

        $payload = $this->validVehiclePayload();
        unset($payload['vehicle_type_id'], $payload['vehicle_brand_id']);

        $this->post(route('vehicles.store'), $payload)
            ->assertInvalid(['vehicle_type_id', 'vehicle_brand_id']);
    }

    public function test_authorized_user_can_create_vehicle_with_fuel_type_relation(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $fuelType = FuelType::factory()->create([
            'name' => 'PERTAMAX',
            'type' => 'bensin',
            'merk' => 'Pertamina',
        ]);

        $payload = $this->validVehiclePayload([
            'fuel_type_id' => $fuelType->id,
            'fuel_type' => 'Legacy Value',
            'plat_new' => 'H 7777 FT',
        ]);

        $this->post(route('vehicles.store'), $payload)
            ->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::query()->where('plat_new', 'H 7777 FT')->firstOrFail();

        $this->assertTrue($vehicle->fuelType->is($fuelType));
        $this->assertTrue($fuelType->vehicles()->whereKey($vehicle->getKey())->exists());
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'fuel_type' => 'PERTAMAX',
        ]);
    }

    public function test_authorized_user_can_create_vehicle_with_vehicle_type_relation(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicleType = VehicleType::factory()->create([
            'code' => 'BOX',
            'name' => 'BOX TRUCK',
        ]);

        $payload = $this->validVehiclePayload([
            'vehicle_type_id' => $vehicleType->id,
            'type' => 'Legacy Type',
            'plat_new' => 'H 8888 VT',
        ]);

        $this->post(route('vehicles.store'), $payload)
            ->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::query()->where('plat_new', 'H 8888 VT')->firstOrFail();

        $this->assertTrue($vehicle->vehicleType->is($vehicleType));
        $this->assertTrue($vehicleType->vehicles()->whereKey($vehicle->getKey())->exists());
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'vehicle_type_id' => $vehicleType->id,
            'type' => 'BOX TRUCK',
        ]);
    }

    public function test_authorized_user_can_create_vehicle_with_vehicle_brand_relation(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicleBrand = VehicleBrand::factory()->create([
            'code' => 'TYT',
            'name' => 'TOYOTA',
        ]);

        $payload = $this->validVehiclePayload([
            'vehicle_brand_id' => $vehicleBrand->id,
            'plat_new' => 'H 8888 VB',
        ]);

        $this->post(route('vehicles.store'), $payload)
            ->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::query()->where('plat_new', 'H 8888 VB')->firstOrFail();

        $this->assertTrue($vehicle->vehicleBrand->is($vehicleBrand));
        $this->assertTrue($vehicleBrand->vehicles()->whereKey($vehicle->getKey())->exists());
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'vehicle_brand_id' => $vehicleBrand->id,
        ]);
    }

    public function test_authorized_user_can_update_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();

        $payload = $this->validVehiclePayload([
            'company_id' => $company->id,
            'type' => 'Updated Truck',
            'plat_new' => $vehicle->plat_new,
            'fuel_type' => 'Solar',
        ]);

        $this->put(route('vehicles.update', $vehicle), $payload)
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'company_id' => $company->id,
            'type' => 'Updated Truck',
            'fuel_type' => 'Solar',
        ]);
    }

    public function test_vehicle_type_and_brand_are_required_when_updating_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());

        $vehicle = Vehicle::factory()->create();
        $payload = $this->validVehiclePayload([
            'plat_new' => $vehicle->plat_new,
        ]);
        unset($payload['vehicle_type_id'], $payload['vehicle_brand_id']);

        $this->put(route('vehicles.update', $vehicle), $payload)
            ->assertInvalid(['vehicle_type_id', 'vehicle_brand_id']);
    }

    public function test_authorized_user_can_view_vehicle_detail_fragment(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create([
            'type' => 'Detail Vehicle',
            'plat_new' => 'H 9999 QA',
        ]);

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('Detail Vehicle')
            ->assertSee('H 9999 QA');
    }

    public function test_show_page_renders_label_link(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create();

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee(route('vehicles.label', $vehicle), false);
    }

    public function test_authorized_user_can_view_vehicle_label(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $company = Company::factory()->create(['name' => 'PT Label Test']);
        $vehicle = Vehicle::factory()->create([
            'company_id' => $company->id,
            'plat_new' => 'H 4321 LBL',
        ]);

        $this->get(route('vehicles.label', $vehicle))
            ->assertOk()
            ->assertSee('H 4321 LBL')
            ->assertSee('PT Label Test')
            ->assertSee(route('qr-scan.index', ['barcode' => 'H 4321 LBL']), false);
    }

    public function test_vehicle_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        Vehicle::factory()->create([
            'type' => 'Data Vehicle',
            'plat_new' => 'H 1235 DT',
        ]);

        $this->get(route('vehicles.data'))
            ->assertOk()
            ->assertJsonFragment(['plat_new' => 'H 1235 DT'])
            ->assertSee('Yakin ingin menghapus kendaraan ini?', false);
    }

    public function test_authorized_user_can_soft_delete_and_restore_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create();

        $this->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);

        $this->post(route('vehicles.restore', $vehicle))
            ->assertRedirect(route('vehicles.trash'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'deleted_at' => null,
        ]);
    }

    public function test_vehicle_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithVehiclePermissions();
        $this->actingAs($user);

        $menuItems = app(MenuService::class)->itemsForContext('desktop', $user);

        $this->assertContains('vehicles-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_vehicle_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.delete']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.restore']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.force_delete']);
    }

    public function test_create_vehicle_with_documents_and_financials(): void
    {
        Storage::fake('local');
        Storage::fake('s3');
        $this->actingAs($this->userWithVehiclePermissions());

        Storage::disk('local')->put('tmp/filepond/a.jpg', $this->fakeImageContents());
        Storage::disk('local')->put('tmp/filepond/b.jpg', $this->fakeImageContents());

        $payload = array_merge($this->validVehiclePayload(), [
            'machine_number' => 'MCH-001',
            'vin_number' => 'VIN-001',
            'purchase_price' => '150000000',
            'selling_price' => '170000000',
            'purchase_date' => '2024-01-15',
            'sale_date' => '2026-02-20',
            'bpkb_image' => ['tmp/filepond/a.jpg', 'tmp/filepond/b.jpg'],
            'bpkb_due_date' => '2027-01-15',
            'stnk_image' => [],
        ]);

        $this->post(route('vehicles.store'), $payload)->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::where('machine_number', 'MCH-001')->firstOrFail();
        $this->assertSame('VIN-001', $vehicle->vin_number);
        $this->assertCount(2, $vehicle->bpkb_image);

        foreach ($vehicle->bpkb_image as $path) {
            $this->assertStringStartsWith('vehicle_documents/', $path);
            Storage::disk('s3')->assertExists($path);
        }

        // The baseline snapshot captured the new fields too.
        $snapshot = $vehicle->snapshots()->first();
        $this->assertSame('MCH-001', $snapshot->machine_number);
        $this->assertCount(2, $snapshot->bpkb_image);
    }

    public function test_update_keeps_existing_and_adds_new_documents(): void
    {
        config(['filesystems.disks.s3.url' => 'https://cdn.test']);
        Storage::fake('local');
        Storage::fake('s3');
        $this->actingAs($this->userWithVehiclePermissions());

        Storage::disk('s3')->put('vehicle_documents/existing.jpg', 'old');
        $vehicle = Vehicle::factory()->create(['bpkb_image' => ['vehicle_documents/existing.jpg']]);

        Storage::disk('local')->put('tmp/filepond/new.jpg', $this->fakeImageContents());

        $payload = array_merge($this->validVehiclePayload(), [
            'plat_new' => $vehicle->plat_new,
            'bpkb_image' => [$vehicle->bpkbImageUrls()[0], 'tmp/filepond/new.jpg'],
            'bpkb_due_date' => '2027-01-15',
            'stnk_image' => [],
        ]);

        $this->put(route('vehicles.update', $vehicle), $payload)->assertRedirect(route('vehicles.index'));

        $vehicle->refresh();
        $this->assertCount(2, $vehicle->bpkb_image);
        $this->assertContains('vehicle_documents/existing.jpg', $vehicle->bpkb_image);
    }

    public function test_document_images_and_due_dates_are_independently_optional_when_creating_vehicle(): void
    {
        Storage::fake('local');
        Storage::fake('s3');
        $this->actingAs($this->userWithVehiclePermissions());

        $datesOnlyPayload = array_merge($this->validVehiclePayload([
            'plat_new' => 'H 1001 DATE',
        ]), [
            'bpkb_due_date' => '2027-01-15',
            'stnk_due_date' => '2027-02-15',
            'kir_due_date' => '2027-03-15',
        ]);

        $this->post(route('vehicles.store'), $datesOnlyPayload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

        $temporaryImages = [
            'bpkb_image' => 'tmp/filepond/bpkb-only.jpg',
            'stnk_image' => 'tmp/filepond/stnk-only.jpg',
            'kir_image' => 'tmp/filepond/kir-only.jpg',
        ];

        foreach ($temporaryImages as $path) {
            Storage::disk('local')->put($path, $this->fakeImageContents());
        }

        $imagesOnlyPayload = $this->validVehiclePayload([
            'plat_new' => 'H 1002 IMAGE',
        ]);

        foreach ($temporaryImages as $field => $path) {
            $imagesOnlyPayload[$field] = [$path];
        }

        $this->post(route('vehicles.store'), $imagesOnlyPayload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

        $datesOnlyVehicle = Vehicle::query()->where('plat_new', 'H 1001 DATE')->firstOrFail();
        $this->assertSame('2027-01-15', $datesOnlyVehicle->bpkb_due_date?->toDateString());
        $this->assertSame('2027-02-15', $datesOnlyVehicle->stnk_due_date?->toDateString());
        $this->assertSame('2027-03-15', $datesOnlyVehicle->kir_due_date?->toDateString());
        $this->assertSame([], $datesOnlyVehicle->bpkb_image);
        $this->assertSame([], $datesOnlyVehicle->stnk_image);
        $this->assertSame([], $datesOnlyVehicle->kir_image);

        $imagesOnlyVehicle = Vehicle::query()->where('plat_new', 'H 1002 IMAGE')->firstOrFail();
        $this->assertNull($imagesOnlyVehicle->bpkb_due_date);
        $this->assertNull($imagesOnlyVehicle->stnk_due_date);
        $this->assertNull($imagesOnlyVehicle->kir_due_date);
        $this->assertCount(1, $imagesOnlyVehicle->bpkb_image);
        $this->assertCount(1, $imagesOnlyVehicle->stnk_image);
        $this->assertCount(1, $imagesOnlyVehicle->kir_image);
    }

    public function test_document_images_and_due_dates_are_independently_optional_when_updating_vehicle(): void
    {
        Storage::fake('local');
        Storage::fake('s3');
        $this->actingAs($this->userWithVehiclePermissions());

        $datesOnlyVehicle = Vehicle::factory()->create([
            'plat_new' => 'H 2001 DATE',
        ]);
        $datesOnlyPayload = array_merge($this->validVehiclePayload([
            'plat_new' => $datesOnlyVehicle->plat_new,
        ]), [
            'bpkb_due_date' => '2028-01-15',
            'stnk_due_date' => '2028-02-15',
            'kir_due_date' => '2028-03-15',
        ]);

        $this->put(route('vehicles.update', $datesOnlyVehicle), $datesOnlyPayload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

        $imagesOnlyVehicle = Vehicle::factory()->create([
            'plat_new' => 'H 2002 IMAGE',
        ]);
        $temporaryImages = [
            'bpkb_image' => 'tmp/filepond/update-bpkb-only.jpg',
            'stnk_image' => 'tmp/filepond/update-stnk-only.jpg',
            'kir_image' => 'tmp/filepond/update-kir-only.jpg',
        ];

        foreach ($temporaryImages as $path) {
            Storage::disk('local')->put($path, $this->fakeImageContents());
        }

        $imagesOnlyPayload = $this->validVehiclePayload([
            'plat_new' => $imagesOnlyVehicle->plat_new,
        ]);

        foreach ($temporaryImages as $field => $path) {
            $imagesOnlyPayload[$field] = [$path];
        }

        $this->put(route('vehicles.update', $imagesOnlyVehicle), $imagesOnlyPayload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

        $datesOnlyVehicle->refresh();
        $this->assertSame('2028-01-15', $datesOnlyVehicle->bpkb_due_date?->toDateString());
        $this->assertSame('2028-02-15', $datesOnlyVehicle->stnk_due_date?->toDateString());
        $this->assertSame('2028-03-15', $datesOnlyVehicle->kir_due_date?->toDateString());
        $this->assertSame([], $datesOnlyVehicle->bpkb_image);
        $this->assertSame([], $datesOnlyVehicle->stnk_image);
        $this->assertSame([], $datesOnlyVehicle->kir_image);

        $imagesOnlyVehicle->refresh();
        $this->assertNull($imagesOnlyVehicle->bpkb_due_date);
        $this->assertNull($imagesOnlyVehicle->stnk_due_date);
        $this->assertNull($imagesOnlyVehicle->kir_due_date);
        $this->assertCount(1, $imagesOnlyVehicle->bpkb_image);
        $this->assertCount(1, $imagesOnlyVehicle->stnk_image);
        $this->assertCount(1, $imagesOnlyVehicle->kir_image);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function validVehiclePayload(array $attributes = []): array
    {
        if (! array_key_exists('vehicle_type_id', $attributes)) {
            $vehicleTypeAttributes = is_string($attributes['type'] ?? null)
                ? ['name' => $attributes['type']]
                : [];

            $attributes['vehicle_type_id'] = VehicleType::factory()->create($vehicleTypeAttributes)->getKey();
        }

        if (! array_key_exists('vehicle_brand_id', $attributes)) {
            $attributes['vehicle_brand_id'] = VehicleBrand::factory()->create()->getKey();
        }

        return Vehicle::factory()->make($attributes)->toArray();
    }

    private function fakeImageContents(): string
    {
        $file = UploadedFile::fake()->image('doc.jpg', 20, 20);

        return (string) file_get_contents($file->getRealPath());
    }

    private function userWithVehiclePermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'vehicle-manager',
            'display_name' => 'Vehicle Manager',
        ]);

        collect([
            'master.vehicles.view',
            'master.vehicles.create',
            'master.vehicles.edit',
            'master.vehicles.delete',
            'master.vehicles.restore',
            'master.vehicles.force_delete',
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
