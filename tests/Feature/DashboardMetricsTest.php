<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Record;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_summary_sums_current_month_maintenance_and_fuel(): void
    {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id, 'status' => 'ACTIVE']);

        Maintenance::create([
            'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
            'no_pol' => $vehicle->plat_new,
            'status' => Maintenance::STATUS_COMPLETED,
            'total_amount' => 750000,
            'isServiceRoutine' => true,
            'mileage' => '10000',
            'date_in' => now()->startOfMonth()->toDateString(),
            'date_out' => now()->startOfMonth()->toDateString(),
        ]);

        // Cancelled maintenance must be excluded from the spend totals.
        Maintenance::create([
            'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
            'no_pol' => $vehicle->plat_new,
            'status' => Maintenance::STATUS_CANCELLED,
            'total_amount' => 999999,
            'isServiceRoutine' => false,
            'mileage' => '11000',
            'date_in' => now()->startOfMonth()->toDateString(),
            'date_out' => now()->startOfMonth()->toDateString(),
        ]);

        Record::factory()->create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicle->id,
            'no_pol' => $vehicle->plat_new,
            'idr' => '250000',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->userWith(['management.tenant.bypass']));

        $summary = app(DashboardMetricsService::class)->financialSummary();

        $this->assertSame(750000.0, $summary['maintenance']['value']);
        $this->assertSame(250000.0, $summary['fuel']['value']);
    }

    public function test_financial_summary_is_company_scoped(): void
    {
        $own = Company::factory()->create();
        $other = Company::factory()->create();

        $ownVehicle = Vehicle::factory()->create(['company_id' => $own->id]);
        $otherVehicle = Vehicle::factory()->create(['company_id' => $other->id]);

        foreach ([$ownVehicle, $otherVehicle] as $vehicle) {
            Maintenance::create([
                'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
                'no_pol' => $vehicle->plat_new,
                'status' => Maintenance::STATUS_COMPLETED,
                'total_amount' => 100000,
                'isServiceRoutine' => true,
                'mileage' => '10000',
                'date_in' => now()->startOfMonth()->toDateString(),
                'date_out' => now()->startOfMonth()->toDateString(),
            ]);
        }

        $this->actingAs($this->userWith(['dashboard.financials.view'], [$own]));

        $summary = app(DashboardMetricsService::class)->financialSummary();

        $this->assertSame(100000.0, $summary['maintenance']['value']);
    }

    public function test_financial_widgets_hidden_without_permission(): void
    {
        $this->actingAs($this->userWith([]));

        $this->get(route('home'))->assertOk()->assertDontSee('Tren Biaya');
    }

    public function test_financial_widgets_visible_with_permission(): void
    {
        $this->actingAs($this->userWith(['dashboard.financials.view']));

        $this->get(route('home'))->assertOk()->assertSee('Tren Biaya');
    }

    public function test_dashboard_header_shows_company_and_current_month(): void
    {
        $company = Company::factory()->create(['name' => 'Tong Tji Unit Test']);

        $this->actingAs($this->userWith([], [$company]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Perusahaan')
            ->assertSee('data-select2-enabled="1"', false)
            ->assertSee('select2@4.0.13', false)
            ->assertDontSee('select2@4.1.0-rc.0', false)
            ->assertSeeText('Tong Tji Unit Test')
            ->assertDontSeeText('Semua Perusahaan')
            ->assertSee('value="'.$company->id.'" selected', false)
            ->assertSeeText('Bulan Aktif')
            ->assertSee('value="'.now()->format('Y-m').'"', false)
            ->assertSee('value="'.now()->locale('id')->translatedFormat('F Y').'"', false);
    }

    public function test_all_companies_option_is_only_visible_for_users_with_multiple_companies(): void
    {
        $firstCompany = Company::factory()->create(['name' => 'First Accessible Company']);
        $secondCompany = Company::factory()->create(['name' => 'Second Accessible Company']);
        Company::factory()->create(['name' => 'Inaccessible Company']);

        $this->actingAs($this->userWith([], [$firstCompany, $secondCompany]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Semua Perusahaan')
            ->assertSeeText('First Accessible Company')
            ->assertSeeText('Second Accessible Company')
            ->assertDontSeeText('Inaccessible Company');
    }

    public function test_dashboard_filters_financial_widgets_by_company_and_month(): void
    {
        $selectedMonth = now()->subMonthNoOverflow()->startOfMonth();
        $selectedCompany = Company::factory()->create(['name' => 'Selected Dashboard Company']);
        $otherCompany = Company::factory()->create(['name' => 'Other Dashboard Company']);
        $selectedVehicle = Vehicle::factory()->create(['company_id' => $selectedCompany->id, 'status' => 'ACTIVE']);
        $otherVehicle = Vehicle::factory()->create(['company_id' => $otherCompany->id, 'status' => 'ACTIVE']);

        Record::factory()->create([
            'company_id' => $selectedCompany->id,
            'vehicle_id' => $selectedVehicle->id,
            'no_pol' => $selectedVehicle->plat_new,
            'idr' => '222000',
            'date' => $selectedMonth->toDateString(),
        ]);

        Record::factory()->create([
            'company_id' => $otherCompany->id,
            'vehicle_id' => $otherVehicle->id,
            'no_pol' => $otherVehicle->plat_new,
            'idr' => '999000',
            'date' => $selectedMonth->toDateString(),
        ]);

        Record::factory()->create([
            'company_id' => $selectedCompany->id,
            'vehicle_id' => $selectedVehicle->id,
            'no_pol' => $selectedVehicle->plat_new,
            'idr' => '333000',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->userWith(['dashboard.financials.view', 'management.tenant.bypass']));

        $this->get(route('home', [
            'company' => $selectedCompany->id,
            'month' => $selectedMonth->format('Y-m'),
        ]))
            ->assertOk()
            ->assertSeeText('Selected Dashboard Company')
            ->assertSee('value="'.$selectedCompany->id.'" selected', false)
            ->assertSee('value="'.$selectedMonth->format('Y-m').'"', false)
            ->assertSeeText('Rp 222.000')
            ->assertDontSeeText('Rp 999.000')
            ->assertDontSeeText('Rp 333.000');
    }

    public function test_dashboard_replaces_vehicle_photo_reminder_with_stnk_and_kir_tables(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $stnkVehicle = Vehicle::factory()->create([
            'company_id' => $company->id,
            'stnk_due_date' => now()->addDays(10)->toDateString(),
        ]);
        $kirVehicle = Vehicle::factory()->create([
            'company_id' => $company->id,
            'kir_due_date' => now()->addDays(5)->toDateString(),
        ]);
        $outsideWindow = Vehicle::factory()->create([
            'company_id' => $company->id,
            'stnk_due_date' => now()->addDays(31)->toDateString(),
        ]);
        $otherCompanyVehicle = Vehicle::factory()->create([
            'company_id' => $otherCompany->id,
            'kir_due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($this->userWith(['master.vehicles.reminders'], [$company]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('STNK Segera Kedaluwarsa')
            ->assertSeeText('KIR Segera Kedaluwarsa')
            ->assertSee($stnkVehicle->plat_new)
            ->assertSee($kirVehicle->plat_new)
            ->assertSee('H-10')
            ->assertSee('H-5')
            ->assertDontSee($outsideWindow->plat_new)
            ->assertDontSee($otherCompanyVehicle->plat_new)
            ->assertDontSeeText('Foto Kendaraan')
            ->assertDontSeeText('Foto Belum Lengkap');
    }

    public function test_dashboard_hides_document_expiry_tables_without_reminder_permission(): void
    {
        $company = Company::factory()->create();
        Vehicle::factory()->create([
            'company_id' => $company->id,
            'stnk_due_date' => now()->addDays(10)->toDateString(),
            'kir_due_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->actingAs($this->userWith([], [$company]));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSeeText('STNK Segera Kedaluwarsa')
            ->assertDontSeeText('KIR Segera Kedaluwarsa');
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWith(array $permissions, array $companies = []): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Dashboard Test Role',
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
