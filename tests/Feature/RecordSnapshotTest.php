<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Record;
use App\Models\RecordSnapshot;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_record_records_a_baseline_snapshot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $record = Record::factory()->create([
            'driver_1' => 'Budi Santoso',
            'no_pol' => 'B 1234 XYZ',
            'destination' => 'Surabaya',
            'finish_km' => '250',
            'date' => '2026-06-01',
        ]);

        $this->assertDatabaseHas('record_snapshots', [
            'record_id' => $record->id,
            'driver_1' => 'Budi Santoso',
            'vehicle_plat' => 'B 1234 XYZ',
            'destination' => 'Surabaya',
            'finish_km' => '250',
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
        ]);

        $record->update([
            'destination' => 'Semarang',
            'finish_km' => '650',
            'totals' => '450',
        ]);

        $this->assertSame(2, RecordSnapshot::where('record_id', $record->id)->count());

        $latest = RecordSnapshot::where('record_id', $record->id)->latest()->first();
        $this->assertSame('Semarang', $latest->destination);
        $this->assertSame('650', $latest->finish_km);
        $this->assertSame('450', $latest->totals);
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

        $record = Record::factory()->create();

        $this->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('Detail Data Perjalanan')
            ->assertSee('Riwayat Perubahan');
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
