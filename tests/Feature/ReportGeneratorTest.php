<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Record;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_day_range_returns_records_inside_range_for_all_companies(): void
    {
        $this->createRecords();

        $this->actingAs($this->userWithPermissions(['reports.generator.view', 'management.tenant.bypass']));

        $this->get(route('report.generator', [
            'type' => 'day',
            'start' => '2026-06-10',
            'end' => '2026-06-30',
        ]))
            ->assertOk()
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return $destinations->contains('A June')
                    && $destinations->contains('B June')
                    && ! $destinations->contains('A May')
                    && ! $destinations->contains('A July');
            })
            ->assertSee('Semua kendaraan');
    }

    public function test_month_range_covers_whole_months(): void
    {
        $this->createRecords();

        $this->actingAs($this->userWithPermissions(['reports.generator.view', 'management.tenant.bypass']));

        $this->get(route('report.generator', [
            'type' => 'month',
            'start' => '2026-05',
            'end' => '2026-06',
        ]))
            ->assertOk()
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return $destinations->contains('A May')
                    && $destinations->contains('A June')
                    && $destinations->contains('B June')
                    && ! $destinations->contains('A July');
            });
    }

    public function test_reversed_range_is_swapped(): void
    {
        $this->createRecords();

        $this->actingAs($this->userWithPermissions(['reports.generator.view', 'management.tenant.bypass']));

        $this->get(route('report.generator', [
            'type' => 'month',
            'start' => '2026-07',
            'end' => '2026-06',
        ]))
            ->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => $filters['start'] === '2026-06' && $filters['end'] === '2026-07');
    }

    public function test_multiple_companies_and_unit_filters(): void
    {
        $data = $this->createRecords();
        $third = Company::factory()->create(['code' => 'CCC', 'name' => 'Company C']);
        $vehicle = Vehicle::factory()->create(['company_id' => $third->id, 'plat_new' => 'B 3000 CCC']);
        Record::factory()->create([
            'company_id' => $third->id,
            'vehicle_id' => $vehicle->id,
            'no_pol' => $vehicle->plat_new,
            'destination' => 'C June',
            'date' => '2026-06-15',
        ]);

        $this->actingAs($this->userWithPermissions(['reports.generator.view', 'management.tenant.bypass']));

        $this->get(route('report.generator', [
            'type' => 'month',
            'start' => '2026-06',
            'end' => '2026-06',
            'companies' => [$data['a']->id, $third->id],
        ]))
            ->assertOk()
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return $destinations->contains('A June')
                    && $destinations->contains('C June')
                    && ! $destinations->contains('B June');
            });

        $this->get(route('report.generator', [
            'type' => 'month',
            'start' => '2026-06',
            'end' => '2026-06',
            'unit' => 'B 2000 BBB',
        ]))
            ->assertOk()
            ->assertViewHas('table', fn ($table): bool => collect($table->clientData())->pluck('tujuan')->all() === ['B June']);
    }

    public function test_companies_outside_user_scope_are_ignored(): void
    {
        $data = $this->createRecords();

        $this->actingAs($this->userWithPermissions(['reports.generator.view'], [$data['a']]));

        $this->get(route('report.generator', [
            'type' => 'month',
            'start' => '2026-06',
            'end' => '2026-06',
            'companies' => [$data['b']->id],
        ]))
            ->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => $filters['companies'] === [])
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return $destinations->contains('A June') && ! $destinations->contains('B June');
            });
    }

    public function test_excel_downloads_for_multiple_companies_and_all_vehicles(): void
    {
        $data = $this->createRecords();

        $this->actingAs($this->userWithPermissions(['reports.generator.export', 'management.tenant.bypass']));

        $this->get(route('report.generator.excel', [
            'type' => 'day',
            'start' => '2026-06-01',
            'end' => '2026-06-30',
            'companies' => [$data['a']->id, $data['b']->id],
        ]))
            ->assertDownload('laporan_bbm_2026_06_01_2026_06_30.xlsx');
    }

    public function test_excel_requires_export_permission(): void
    {
        $this->actingAs($this->userWithPermissions(['reports.generator.view']));

        $this->get(route('report.generator.excel'))
            ->assertRedirect()
            ->assertHeaderMissing('content-disposition');
    }

    /**
     * @return array{a: Company, b: Company}
     */
    private function createRecords(): array
    {
        $a = Company::factory()->create(['code' => 'AAA', 'name' => 'Company A']);
        $b = Company::factory()->create(['code' => 'BBB', 'name' => 'Company B']);
        $vehicleA = Vehicle::factory()->create(['company_id' => $a->id, 'plat_new' => 'B 1000 AAA']);
        $vehicleB = Vehicle::factory()->create(['company_id' => $b->id, 'plat_new' => 'B 2000 BBB']);

        foreach ([
            [$a, $vehicleA, 'A May', '2026-05-20'],
            [$a, $vehicleA, 'A June', '2026-06-10'],
            [$b, $vehicleB, 'B June', '2026-06-30'],
            [$a, $vehicleA, 'A July', '2026-07-01'],
        ] as [$company, $vehicle, $destination, $date]) {
            Record::factory()->create([
                'company_id' => $company->id,
                'vehicle_id' => $vehicle->id,
                'no_pol' => $vehicle->plat_new,
                'destination' => $destination,
                'date' => $date,
            ]);
        }

        return ['a' => $a, 'b' => $b];
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWithPermissions(array $permissions, array $companies = []): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Report Generator Test Role',
        ]);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $name, 'module' => str($name)->before('.')->value()],
            );

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user->roles()->attach($role->id, ['assigned_at' => now()]);

        if ($companies !== []) {
            $user->companies()->sync(collect($companies)->map(fn (Company $company): int => $company->id)->all());
        }

        $user->load('roles.permissions');

        return $user;
    }
}
