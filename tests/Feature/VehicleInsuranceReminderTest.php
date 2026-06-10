<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use App\Services\VehicleInsuranceMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleInsuranceReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_reminders_permission_sees_only_own_company_expiring(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $expiringA = $this->makeExpiringInsurance($companyA, 10);
        $expiringB = $this->makeExpiringInsurance($companyB, 10);

        $this->actingAs($this->userWith(['master.vehicle_insurances.reminders'], [$companyA]));

        $plates = $this->expiringPlates();

        $this->assertContains($expiringA->vehicle->plat_new, $plates);
        $this->assertNotContains($expiringB->vehicle->plat_new, $plates);
    }

    public function test_user_without_reminders_permission_sees_no_expiring(): void
    {
        $company = Company::factory()->create();
        $expiring = $this->makeExpiringInsurance($company, 10);

        // Has insurance view but NOT the dedicated reminders permission.
        $this->actingAs($this->userWith(['master.vehicle_insurances.view'], [$company]));

        $this->assertNotContains($expiring->vehicle->plat_new, $this->expiringPlates());
    }

    public function test_tenant_bypass_user_sees_expiring_from_all_companies(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $expiringA = $this->makeExpiringInsurance($companyA, 5);
        $expiringB = $this->makeExpiringInsurance($companyB, 5);

        $this->actingAs($this->userWith(
            ['master.vehicle_insurances.reminders', 'management.tenant.bypass'],
            [],
        ));

        $plates = $this->expiringPlates();

        $this->assertContains($expiringA->vehicle->plat_new, $plates);
        $this->assertContains($expiringB->vehicle->plat_new, $plates);
    }

    public function test_expired_and_far_future_policies_are_excluded(): void
    {
        $company = Company::factory()->create();
        $within = $this->makeExpiringInsurance($company, 10);
        $expired = $this->makeExpiringInsurance($company, -1);
        $far = $this->makeExpiringInsurance($company, 60);

        $this->actingAs($this->userWith(['master.vehicle_insurances.reminders', 'management.tenant.bypass'], []));

        $plates = $this->expiringPlates();

        $this->assertContains($within->vehicle->plat_new, $plates);
        $this->assertNotContains($expired->vehicle->plat_new, $plates);
        $this->assertNotContains($far->vehicle->plat_new, $plates);
    }

    public function test_remaining_days_map_to_the_tightest_threshold_bucket(): void
    {
        $company = Company::factory()->create();
        $this->actingAs($this->userWith(['master.vehicle_insurances.reminders', 'management.tenant.bypass'], []));

        $cases = [20 => 30, 10 => 14, 5 => 7, 2 => 3];

        foreach ($cases as $daysLeft => $expectedThreshold) {
            $insurance = $this->makeExpiringInsurance($company, $daysLeft);

            $policy = app(VehicleInsuranceMonitorService::class)
                ->expiringPolicies()
                ->firstWhere('insurance_id', $insurance->id);

            $this->assertNotNull($policy, "Expected a reminder for H-{$daysLeft}");
            $this->assertSame($expectedThreshold, $policy['threshold'], "H-{$daysLeft} should bucket to {$expectedThreshold}");

            $insurance->forceDelete();
        }
    }

    public function test_overdue_policy_is_reported_as_overdue(): void
    {
        $company = Company::factory()->create();
        $overdue = $this->makeExpiringInsurance($company, -5);

        $this->actingAs($this->userWith(['master.vehicle_insurances.reminders', 'management.tenant.bypass'], []));

        $this->assertContains($overdue->vehicle->plat_new, $this->overduePlates());
        $this->assertNotContains($overdue->vehicle->plat_new, $this->expiringPlates());
    }

    public function test_renewed_vehicle_is_not_flagged_overdue_for_the_old_policy(): void
    {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id, 'status' => 'ACTIVE']);

        // An old, expired policy plus a current, still-valid renewal for the same vehicle.
        VehicleInsurance::factory()->create([
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->subYears(2)->toDateString(),
            'end_date' => now()->subYear()->toDateString(),
        ]);
        VehicleInsurance::factory()->create([
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->addMonths(10)->toDateString(),
        ]);

        $this->actingAs($this->userWith(['master.vehicle_insurances.reminders', 'management.tenant.bypass'], []));

        $this->assertNotContains($vehicle->plat_new, $this->overduePlates());
    }

    /**
     * Plates of the insurance-expiring notifications returned for the acting user.
     *
     * @return list<string>
     */
    private function expiringPlates(): array
    {
        return $this->notificationPlates('vehicle.insurance-expiring');
    }

    /**
     * Plates of the insurance-overdue notifications returned for the acting user.
     *
     * @return list<string>
     */
    private function overduePlates(): array
    {
        return $this->notificationPlates('vehicle.insurance-overdue');
    }

    /**
     * @return list<string>
     */
    private function notificationPlates(string $type): array
    {
        return collect($this->getJson(route('notifications.index'))->assertOk()->json('items'))
            ->where('type', $type)
            ->pluck('title')
            ->all();
    }

    private function makeExpiringInsurance(Company $company, int $daysFromNow): VehicleInsurance
    {
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id, 'status' => 'ACTIVE']);
        $endDate = now()->startOfDay()->addDays($daysFromNow);

        return VehicleInsurance::factory()->create([
            'vehicle_id' => $vehicle->id,
            'start_date' => $endDate->copy()->subYear()->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);
    }

    /**
     * @param  list<string>  $permissions
     * @param  list<Company>  $companies
     */
    private function userWith(array $permissions, array $companies): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => fake()->unique()->slug(),
            'display_name' => 'Reminder Test Role',
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
