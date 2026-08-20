<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Record;
use App\Models\RecordSnapshot;
use App\Models\Role;
use App\Models\User;
use App\Services\RecordFuelCostCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_factory_keeps_unit_price_and_total_coherent(): void
    {
        $record = Record::factory()->create();

        $this->assertSame(
            app(RecordFuelCostCalculator::class)->calculate($record->fuel_unit_price, $record->bbm),
            $record->idr,
        );
    }

    public function test_creating_a_record_records_a_baseline_snapshot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $record = Record::factory()->create([
            'driver_1' => 'Budi Santoso',
            'no_pol' => 'B 1234 XYZ',
            'destination' => 'Surabaya',
            'finish_km' => '250',
            'fuel_unit_price' => '14750.55',
            'date' => '2026-06-01',
        ]);

        $this->assertDatabaseHas('record_snapshots', [
            'record_id' => $record->id,
            'driver_1' => 'Budi Santoso',
            'vehicle_plat' => 'B 1234 XYZ',
            'destination' => 'Surabaya',
            'finish_km' => '250',
            'fuel_unit_price' => '14750.55',
            'changed_by' => $user->id,
        ]);
        $this->assertSame(1, RecordSnapshot::where('record_id', $record->id)->count());
    }

    public function test_updating_a_record_records_a_new_full_snapshot(): void
    {
        $this->actingAs(User::factory()->create());

        $record = Record::factory()->create([
            'destination' => 'Bandung',
            'finish_km' => '500',
            'totals' => '300',
            'fuel_unit_price' => '12000.25',
        ]);

        $record->update([
            'destination' => 'Semarang',
            'finish_km' => '650',
            'totals' => '450',
            'fuel_unit_price' => '12345.67',
        ]);

        $this->assertSame(2, RecordSnapshot::where('record_id', $record->id)->count());

        $latest = RecordSnapshot::where('record_id', $record->id)->latest()->first();
        $this->assertSame('Semarang', $latest->destination);
        $this->assertSame('650', $latest->finish_km);
        $this->assertSame('450', $latest->totals);
        $this->assertSame('12345.67', $latest->fuel_unit_price);
    }

    public function test_a_no_op_update_records_no_snapshot(): void
    {
        $this->actingAs(User::factory()->create());

        $record = Record::factory()->create(['destination' => 'Malang']);

        $record->update(['destination' => 'Malang']);

        $this->assertSame(1, RecordSnapshot::where('record_id', $record->id)->count());
    }

    public function test_show_page_renders_the_snapshot_table(): void
    {
        $this->actingAs($this->userWithRecordView());

        $record = Record::factory()->create(['fuel_unit_price' => '14750.55']);

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('Detail Data Perjalanan')
            ->assertSee('Riwayat Perubahan')
            ->assertSee('Jumlah BBM (Liter)')
            ->assertSee('Harga per Liter')
            ->assertSee('Rp 14.750,55');
    }

    public function test_snapshot_history_supports_legacy_null_unit_price(): void
    {
        $this->actingAs($this->userWithRecordView());

        $record = Record::factory()->create(['fuel_unit_price' => null]);

        $snapshot = RecordSnapshot::where('record_id', $record->id)->sole();
        $this->assertNull($snapshot->fuel_unit_price);

        $this->getJson(route('records.snapshots.data', [
            'record' => $record,
            'engine' => 'tabulator',
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.fuel_unit_price_label', '-');
    }

    private function userWithRecordView(): User
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
}
