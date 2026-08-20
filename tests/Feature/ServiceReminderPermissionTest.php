<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceReminderPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_reminders_permission_sees_only_own_company_service_due(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $dueA = $this->makeDueVehicle($companyA);
        $dueB = $this->makeDueVehicle($companyB);

        $this->actingAs($this->userWith(['operational.maintenances.reminders'], [$companyA]));

        $plates = $this->serviceDuePlates();

        $this->assertContains($dueA->plat_new, $plates);
        $this->assertNotContains($dueB->plat_new, $plates);
    }

    public function test_dashboard_service_reminder_has_view_all_action(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->userWith(['operational.maintenances.reminders'], [$company]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'Pengingat Servis',
                'href="'.route('maintenances.index').'"',
                'Lihat Semua',
                'dashServiceReminder',
            ], false);
    }

    public function test_user_without_reminders_permission_sees_no_service_due(): void
    {
        $company = Company::factory()->create();
        $due = $this->makeDueVehicle($company);

        // Has maintenance view (old gate) but NOT the dedicated reminders permission.
        $this->actingAs($this->userWith(['operational.maintenances.view'], [$company]));

        $this->assertNotContains($due->plat_new, $this->serviceDuePlates());
    }

    public function test_tenant_bypass_user_sees_service_due_from_all_companies(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $dueA = $this->makeDueVehicle($companyA);
        $dueB = $this->makeDueVehicle($companyB);

        $this->actingAs($this->userWith(
            ['operational.maintenances.reminders', 'management.tenant.bypass'],
            [],
        ));

        $plates = $this->serviceDuePlates();

        $this->assertContains($dueA->plat_new, $plates);
        $this->assertContains($dueB->plat_new, $plates);
    }

    /**
     * Plates of the service-due notifications returned for the acting user.
     *
     * @return list<string>
     */
    private function serviceDuePlates(): array
    {
        return collect($this->getJson(route('notifications.index'))->assertOk()->json('items'))
            ->where('type', 'vehicle.service-due')
            ->pluck('title')
            ->all();
    }

    /**
     * An ACTIVE vehicle that is overdue: current mileage exceeds the last completed
     * routine service mileage plus the service interval.
     */
    private function makeDueVehicle(Company $company): Vehicle
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => $company->id,
            'status' => 'ACTIVE',
            'mileage' => '25000',
        ]);

        Maintenance::create([
            'code_mt' => 'MT-'.fake()->unique()->numerify('#####'),
            'no_pol' => $vehicle->plat_new,
            'isServiceRoutine' => true,
            'status' => Maintenance::STATUS_COMPLETED,
            'mileage' => '10000',
            'date_in' => now()->subMonths(2)->toDateString(),
            'date_out' => now()->subMonths(2)->toDateString(),
        ]);

        return $vehicle;
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
