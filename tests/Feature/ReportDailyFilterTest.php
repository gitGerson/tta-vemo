<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Record;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DailyPdfTotalCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDailyFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_report_all_companies_returns_all_accessible_records_for_bypass_user(): void
    {
        $records = $this->createDailyRecords();

        $this->actingAs($this->userWithPermissions([
            'reports.daily.view',
            'management.tenant.bypass',
        ]));

        $this->get(route('report.daily', [
            'date' => $records['date'],
            'company' => '',
        ]))
            ->assertOk()
            ->assertViewHas('QueryCompany', null)
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return $destinations->contains('Default Destination')
                    && $destinations->contains('Other Destination');
            })
            ->assertSee('data-select2-enabled="1"', false)
            ->assertSee('select2@4.0.13', false)
            ->assertDontSee('select2@4.1.0-rc.0', false)
            ->assertDontSee('name="category"', false)
            ->assertSee('value="" selected', false);
    }

    public function test_daily_report_all_companies_is_limited_to_user_companies(): void
    {
        $records = $this->createDailyRecords();

        $this->actingAs($this->userWithPermissions(
            ['reports.daily.view'],
            [$records['defaultCompany']],
        ));

        $this->get(route('report.daily', [
            'date' => $records['date'],
            'company' => '',
        ]))
            ->assertOk()
            ->assertViewHas('QueryCompany', null)
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return $destinations->contains('Default Destination')
                    && ! $destinations->contains('Other Destination');
            });
    }

    public function test_daily_report_selected_company_returns_only_that_company(): void
    {
        $records = $this->createDailyRecords();

        $this->actingAs($this->userWithPermissions([
            'reports.daily.view',
            'management.tenant.bypass',
        ]));

        $this->get(route('report.daily', [
            'date' => $records['date'],
            'company' => $records['otherCompany']->id,
        ]))
            ->assertOk()
            ->assertViewHas('QueryCompany', $records['otherCompany']->id)
            ->assertViewHas('table', function ($table): bool {
                $destinations = collect($table->clientData())->pluck('tujuan');

                return ! $destinations->contains('Default Destination')
                    && $destinations->contains('Other Destination');
            });
    }

    public function test_daily_report_print_button_is_disabled_for_all_companies(): void
    {
        $records = $this->createDailyRecords();

        $this->actingAs($this->userWithPermissions([
            'reports.daily.view',
            'management.tenant.bypass',
        ]));

        $this->get(route('report.daily', [
            'date' => $records['date'],
            'company' => '',
        ]))
            ->assertOk()
            ->assertSee('Pilih satu perusahaan untuk mencetak')
            ->assertSee('aria-disabled="true"', false);
    }

    public function test_daily_pdf_requires_a_selected_company(): void
    {
        $this->actingAs($this->userWithPermissions([
            'reports.daily.export',
            'management.tenant.bypass',
        ]));

        $this->from(route('report.daily'))
            ->get(route('report.daily.pdf', [
                'date' => '2026-06-23',
                'company' => '',
            ]))
            ->assertRedirect(route('report.daily'))
            ->assertSessionHasErrors('company');
    }

    public function test_daily_pdf_with_selected_company_reaches_pdf_response(): void
    {
        $records = $this->createDailyRecords();

        $this->actingAs($this->userWithPermissions([
            'reports.daily.export',
            'management.tenant.bypass',
        ]));

        $this->get(route('report.daily.pdf', [
            'date' => $records['date'],
            'company' => $records['defaultCompany']->id,
        ]))
            ->assertDownload("laporan_harian_{$records['date']}.pdf");
    }

    public function test_daily_pdf_view_rounds_each_record_before_summing_idr(): void
    {
        $date = '2026-06-23';
        $vehicle = Vehicle::factory()->make(['plat_new' => 'B 1000 TTI']);
        $records = collect([
            Record::factory()->make([
                'no_pol' => $vehicle->plat_new,
                'date' => $date,
                'idr' => '6643627',
            ]),
            Record::factory()->make([
                'no_pol' => $vehicle->plat_new,
                'date' => $date,
                'idr' => '100',
            ]),
        ]);
        $idrSummary = app(DailyPdfTotalCalculator::class)->calculate($records->pluck('idr'));

        $html = view('reports.pdf.daily', [
            'records' => $records,
            'date' => $date,
            'vehicles' => collect([$vehicle]),
            'company' => Company::factory()->make(['name' => 'Default Company']),
            'idrSummary' => $idrSummary,
        ])->render();

        $this->assertStringContainsString('<td class="text-right">6.643.700</td>', $html);
        $this->assertStringContainsString('<td class="text-right">100</td>', $html);
        $this->assertStringContainsString('<strong>6.643.800</strong>', $html);
        $this->assertStringNotContainsString('PENYESUAIAN PEMBULATAN', $html);
    }

    public function test_maintenance_year_exports_require_a_selected_company(): void
    {
        $this->actingAs($this->userWithPermissions([
            'reports.maintenances.year.export',
            'management.tenant.bypass',
        ]));

        $this->from(route('report.maintenances.year'))
            ->get(route('report.maintenances.year.pdf', [
                'year' => 2026,
                'company' => '',
            ]))
            ->assertRedirect(route('report.maintenances.year'))
            ->assertSessionHasErrors('company');

        $this->from(route('report.maintenances.year'))
            ->get(route('report.maintenances.year.excel', [
                'year' => 2026,
                'company' => '',
            ]))
            ->assertRedirect(route('report.maintenances.year'))
            ->assertSessionHasErrors('company');
    }

    /**
     * @return array{
     *     date: string,
     *     defaultCompany: Company,
     *     otherCompany: Company
     * }
     */
    private function createDailyRecords(): array
    {
        $date = '2026-06-23';
        $defaultCompany = Company::factory()->create(['code' => 'TTI', 'name' => 'Default Company']);
        $otherCompany = Company::factory()->create(['code' => 'ALT', 'name' => 'Other Company']);

        $defaultVehicle = Vehicle::factory()->create([
            'company_id' => $defaultCompany->id,
            'plat_new' => 'B 1000 TTI',
        ]);
        $otherVehicle = Vehicle::factory()->create([
            'company_id' => $otherCompany->id,
            'plat_new' => 'B 2000 ALT',
        ]);

        Record::factory()->create([
            'company_id' => $defaultCompany->id,
            'vehicle_id' => $defaultVehicle->id,
            'no_pol' => $defaultVehicle->plat_new,
            'destination' => 'Default Destination',
            'date' => $date,
        ]);
        Record::factory()->create([
            'company_id' => $otherCompany->id,
            'vehicle_id' => $otherVehicle->id,
            'no_pol' => $otherVehicle->plat_new,
            'destination' => 'Other Destination',
            'date' => $date,
        ]);

        return [
            'date' => $date,
            'defaultCompany' => $defaultCompany,
            'otherCompany' => $otherCompany,
        ];
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
            'display_name' => 'Report Daily Test Role',
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
