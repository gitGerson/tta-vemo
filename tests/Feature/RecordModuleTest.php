<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\FuelType;
use App\Models\Permission;
use App\Models\Record;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecordModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithRecordPermissions());
        FuelType::factory()->create([
            'name' => 'RON 95',
            'type' => 'bensin',
            'price' => 15000.25,
            'merk' => 'BP',
        ]);

        $this->get(route('records.index'))
            ->assertOk()
            ->assertSee('Data Perjalanan')
            ->assertSee('record-modal')
            ->assertSee('Tambah Catatan Perjalanan')
            ->assertSee('Jumlah BBM (Liter)')
            ->assertSee('Harga per Liter')
            ->assertSee('Total Harga')
            ->assertSee('RON 95 (BP - Bensin)')
            ->assertSee('15000.25')
            ->assertSee('name="fuel_unit_price"', false)
            ->assertSee('step="0.01"', false)
            ->assertSee('max="'.FuelType::MAX_PRICE.'"', false)
            ->assertSee('data-record-fuel-pricing-form', false);
    }

    public function test_create_and_edit_pages_redirect_to_index_because_forms_are_modal_based(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $record = Record::factory()->create();

        $this->get(route('records.create'))->assertRedirect(route('records.index'));
        $this->get(route('records.edit', $record))->assertRedirect(route('records.index'));
    }

    public function test_show_page_renders_the_edit_modal_trigger_for_an_editor(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $record = Record::factory()->create(['no_pol' => 'B 3 GH']);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('btn-edit-record')
            ->assertSee('record-modal')
            ->assertSee('data-update-url="'.route('records.update', $record).'"', false)
            ->assertSee('data-no-pol="B 3 GH"', false)
            ->assertSee('name="origin" value="show"', false);
    }

    public function test_show_page_hides_the_edit_modal_from_a_view_only_user(): void
    {
        $this->actingAs($this->userWithRecordViewOnly());

        $record = Record::factory()->create();

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('btn-edit-record')
            ->assertDontSee('record-modal');
    }

    public function test_index_forbidden_for_unauthorized_user(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('records.index'))->assertRedirect();
    }

    public function test_store_uses_submitted_unit_price_and_ignores_submitted_total(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $this->createProvince();
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 1234 XYZ', 'mileage' => '100']);
        $driver = Driver::factory()->create(['name' => 'Budi Santoso']);
        $fuelType = FuelType::factory()->create(['price' => 15000.25]);
        $destination = Destination::factory()->create(['name' => 'Surabaya']);

        $payload = [
            'company_id' => $company->id,
            'fuel_type_id' => $fuelType->id,
            'date' => '01-06-2026',
            'driver_1' => $driver->id,
            'driver_2' => null,
            'no_pol' => $vehicle->plat_new,
            'destination' => $destination->name,
            'start_km' => '100',
            'finish_km' => '250',
            'bbm' => '30.5',
            'fuel_unit_price' => '14750.55',
            'idr' => '1',
        ];

        $this->post(route('records.store'), $payload)
            ->assertRedirect(route('records.index'));

        $this->assertDatabaseHas('records', [
            'no_pol' => 'B 1234 XYZ',
            'driver_1' => 'Budi Santoso',
            'destination' => 'Surabaya',
            'company_id' => $company->id,
            'date' => '2026-06-01 00:00:00',
            'start_km' => '100',
            'finish_km' => '250',
            'totals' => '150',
            'scale' => '5',
            'fuel_unit_price' => '14750.55',
            'idr' => '449891.78',
        ]);

        $this->assertSame('250', $vehicle->fresh()->mileage);
    }

    public function test_store_rejects_invalid_fuel_inputs(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $this->createProvince();
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $driver = Driver::factory()->create();
        $fuelType = FuelType::factory()->create(['price' => '15000.25']);
        $deletedFuelType = FuelType::factory()->create();
        $destination = Destination::factory()->create();
        $deletedFuelType->delete();

        $payload = [
            'company_id' => $company->id,
            'fuel_type_id' => $fuelType->id,
            'date' => '01-06-2026',
            'driver_1' => $driver->id,
            'no_pol' => $vehicle->plat_new,
            'destination' => $destination->name,
            'finish_km' => '250',
            'bbm' => '10',
            'fuel_unit_price' => '10000',
        ];

        $this->post(route('records.store'), [
            ...$payload,
            'fuel_type_id' => $deletedFuelType->id,
        ])->assertSessionHasErrors(['fuel_type_id']);

        $missingUnitPrice = $payload;
        unset($missingUnitPrice['fuel_unit_price']);

        $this->post(route('records.store'), $missingUnitPrice)
            ->assertSessionHasErrors(['fuel_unit_price']);

        $negativePriceResponse = $this->post(route('records.store'), [
            ...$payload,
            'fuel_unit_price' => '-1',
        ]);
        $negativePriceResponse
            ->assertSessionHasErrors(['fuel_unit_price'])
            ->assertSessionHasInput('fuel_unit_price', '-1');

        $this->post(route('records.store'), [
            ...$payload,
            'fuel_unit_price' => 'not-a-number',
        ])->assertSessionHasErrors(['fuel_unit_price']);

        $this->post(route('records.store'), [
            ...$payload,
            'fuel_unit_price' => '10000.001',
        ])->assertSessionHasErrors(['fuel_unit_price']);

        $this->post(route('records.store'), [
            ...$payload,
            'fuel_unit_price' => '10000000000000',
        ])->assertSessionHasErrors(['fuel_unit_price']);

        $this->assertDatabaseEmpty('records');
    }

    public function test_data_endpoint_renders_record_edit_modal_action(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $this->createProvince();
        $company = Company::factory()->create();
        $vehicleType = VehicleType::factory()->create(['name' => 'BOX TRUCK']);
        $vehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $vehicleType->id,
            'plat_new' => 'B 4321 XYZ',
        ]);
        $driver = Driver::factory()->create(['name' => 'Slamet']);
        $fuelType = FuelType::factory()->create(['price' => '15000.25']);

        Record::factory()->create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'driver_1' => $driver->name,
            'no_pol' => $vehicle->plat_new,
            'destination' => 'Jakarta Barat',
            'date' => '2026-06-04',
            'fuel_unit_price' => '12345.67',
        ]);

        $this->getJson(route('records.data', ['engine' => 'tabulator']))
            ->assertOk()
            ->assertJsonPath('data.0.vehicle_type_label', 'BOX TRUCK')
            ->assertJsonPath('data.0.action', fn (string $action): bool => str_contains($action, 'btn-edit-record')
                && str_contains($action, 'data-update-url=')
                && str_contains($action, 'data-driver1="'.$driver->id.'"')
                && str_contains($action, 'data-fuel-unit-price="12345.67"'));
    }

    public function test_store_defaults_start_km_from_last_record_finish_km(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $this->createProvince();
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 9 AA', 'mileage' => '0']);
        $driver = Driver::factory()->create(['name' => 'Joko']);
        $fuelType = FuelType::factory()->create(['price' => 10000]);
        $destination = Destination::factory()->create(['name' => 'Malang']);

        Record::factory()->create([
            'no_pol' => $vehicle->plat_new,
            'vehicle_id' => $vehicle->id,
            'company_id' => $company->id,
            'finish_km' => '500',
        ]);

        $this->post(route('records.store'), [
            'company_id' => $company->id,
            'fuel_type_id' => $fuelType->id,
            'date' => '2026-06-02',
            'driver_1' => $driver->id,
            'no_pol' => $vehicle->plat_new,
            'destination' => $destination->name,
            'finish_km' => '650',
            'bbm' => '15',
            'fuel_unit_price' => '9750.50',
        ])->assertRedirect(route('records.index'));

        // start_km falls back to the previous record's finish_km (500); totals 150.
        $this->assertDatabaseHas('records', [
            'no_pol' => 'B 9 AA',
            'start_km' => '500',
            'finish_km' => '650',
            'totals' => '150',
        ]);
    }

    public function test_update_preserves_custom_unit_price_and_recomputes_total(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $this->createProvince();
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 7 CD', 'mileage' => '0']);
        $driver = Driver::factory()->create(['name' => 'Andi']);
        $fuelType = FuelType::factory()->create(['price' => 12000]);
        $destination = Destination::factory()->create(['name' => 'Bandung']);

        $record = Record::factory()->create([
            'no_pol' => $vehicle->plat_new,
            'vehicle_id' => $vehicle->id,
            'company_id' => $company->id,
        ]);

        $this->put(route('records.update', $record), [
            'company_id' => $company->id,
            'fuel_type_id' => $fuelType->id,
            'date' => '2026-06-03',
            'driver_1' => $driver->id,
            'no_pol' => $vehicle->plat_new,
            'destination' => $destination->name,
            'start_km' => '200',
            'finish_km' => '500',
            'bbm' => '30',
            'fuel_unit_price' => '12345.67',
            'idr' => '1',
        ])->assertRedirect(route('records.index'));

        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'totals' => '300',
            'scale' => '10',
            'fuel_unit_price' => '12345.67',
            'idr' => '370370.10',
        ]);
        $this->assertSame('500', $vehicle->fresh()->mileage);
    }

    public function test_update_from_the_detail_page_redirects_back_to_it(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $this->createProvince();
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 8 IJ', 'mileage' => '0']);
        $driver = Driver::factory()->create(['name' => 'Rina']);
        $fuelType = FuelType::factory()->create(['price' => 12000]);
        $destination = Destination::factory()->create(['name' => 'Semarang']);

        $record = Record::factory()->create([
            'no_pol' => $vehicle->plat_new,
            'vehicle_id' => $vehicle->id,
            'company_id' => $company->id,
        ]);

        $this->put(route('records.update', $record), [
            'origin' => 'show',
            'company_id' => $company->id,
            'fuel_type_id' => $fuelType->id,
            'date' => '2026-06-03',
            'driver_1' => $driver->id,
            'no_pol' => $vehicle->plat_new,
            'destination' => $destination->name,
            'start_km' => '200',
            'finish_km' => '500',
            'bbm' => '30',
            'fuel_unit_price' => '12345.67',
        ])->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('records', ['id' => $record->id, 'totals' => '300']);
    }

    public function test_destroy_reverts_vehicle_mileage_and_soft_deletes(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 5 EF', 'mileage' => '900']);
        $record = Record::factory()->create([
            'no_pol' => $vehicle->plat_new,
            'vehicle_id' => $vehicle->id,
            'start_km' => '300',
        ]);

        $this->delete(route('records.destroy', $record))
            ->assertRedirect(route('records.index'));

        $this->assertSoftDeleted($record);
        $this->assertSame('300', $vehicle->fresh()->mileage);
    }

    public function test_authorized_user_can_restore_record(): void
    {
        $this->actingAs($this->userWithRecordPermissions());

        $record = Record::factory()->create();
        $record->delete();

        $this->post(route('records.restore', $record))
            ->assertRedirect(route('records.trash'));

        $this->assertDatabaseHas('records', ['id' => $record->id, 'deleted_at' => null]);
    }

    private function userWithRecordPermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'record-manager',
            'display_name' => 'Record Manager',
        ]);

        collect([
            'operational.records.view',
            'operational.records.create',
            'operational.records.edit',
            'operational.records.delete',
            'operational.records.restore',
            'operational.records.force_delete',
            'management.tenant.bypass',
        ])->each(function (string $name) use ($role): void {
            $permission = Permission::create([
                'name' => $name,
                'display_name' => $name,
                'module' => str($name)->before('.')->value(),
            ]);

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }

    private function userWithRecordViewOnly(): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'record-viewer', 'display_name' => 'Record Viewer']);

        collect(['operational.records.view', 'management.tenant.bypass'])->each(function (string $name) use ($role): void {
            $permission = Permission::create([
                'name' => $name,
                'display_name' => $name,
                'module' => str($name)->before('.')->value(),
            ]);

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }

    private function createProvince(): void
    {
        DB::table('indonesia_provinces')->insertOrIgnore([
            'code' => '31',
            'name' => 'DKI Jakarta',
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
