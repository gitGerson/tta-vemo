<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleDocumentMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleDocumentReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_reminders_permission_sees_only_own_company_documents(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $stnkA = $this->makeVehicle($companyA, ['stnk_due_date' => $this->inDays(10)]);
        $stnkB = $this->makeVehicle($companyB, ['stnk_due_date' => $this->inDays(10)]);

        $this->actingAs($this->userWith(['master.vehicles.reminders'], [$companyA]));

        $plates = $this->duePlates('vehicle.stnk-due');

        $this->assertContains($stnkA->plat_new, $plates);
        $this->assertNotContains($stnkB->plat_new, $plates);
    }

    public function test_user_without_reminders_permission_sees_no_documents(): void
    {
        $company = Company::factory()->create();
        $vehicle = $this->makeVehicle($company, ['stnk_due_date' => $this->inDays(10)]);

        // Has vehicle view but NOT the dedicated reminders permission.
        $this->actingAs($this->userWith(['master.vehicles.view'], [$company]));

        $this->assertNotContains($vehicle->plat_new, $this->duePlates('vehicle.stnk-due'));
    }

    public function test_tenant_bypass_user_sees_documents_from_all_companies(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $stnkA = $this->makeVehicle($companyA, ['stnk_due_date' => $this->inDays(5)]);
        $stnkB = $this->makeVehicle($companyB, ['stnk_due_date' => $this->inDays(5)]);

        $this->actingAs($this->userWith(['master.vehicles.reminders', 'management.tenant.bypass'], []));

        $plates = $this->duePlates('vehicle.stnk-due');

        $this->assertContains($stnkA->plat_new, $plates);
        $this->assertContains($stnkB->plat_new, $plates);
    }

    public function test_stnk_fires_within_thirty_days_and_remains_visible_when_overdue(): void
    {
        $company = Company::factory()->create();
        $within = $this->makeVehicle($company, ['stnk_due_date' => $this->inDays(20)]);
        $boundary = $this->makeVehicle($company, ['stnk_due_date' => $this->inDays(30)]);
        $beyond = $this->makeVehicle($company, ['stnk_due_date' => $this->inDays(40)]);
        $overdue = $this->makeVehicle($company, ['stnk_due_date' => $this->inDays(-1)]);

        $this->actingAs($this->userWith(['master.vehicles.reminders', 'management.tenant.bypass'], []));

        $plates = $this->duePlates('vehicle.stnk-due');

        $this->assertContains($within->plat_new, $plates);
        $this->assertContains($boundary->plat_new, $plates);
        $this->assertNotContains($beyond->plat_new, $plates);
        $this->assertContains($overdue->plat_new, $plates);

        $overdueDocument = app(VehicleDocumentMonitorService::class)
            ->dueDocuments()
            ->firstWhere('plate', $overdue->plat_new);

        $this->assertSame('overdue', $overdueDocument['state']);
        $this->assertSame(-1, $overdueDocument['days_left']);
        $this->assertSame(1, $overdueDocument['days_overdue']);
    }

    public function test_kir_only_fires_within_seven_days(): void
    {
        $company = Company::factory()->create();
        $within = $this->makeVehicle($company, ['kir_due_date' => $this->inDays(5)]);
        $beyond = $this->makeVehicle($company, ['kir_due_date' => $this->inDays(20)]);
        $overdue = $this->makeVehicle($company, ['kir_due_date' => $this->inDays(-24)]);

        $this->actingAs($this->userWith(['master.vehicles.reminders', 'management.tenant.bypass'], []));

        $plates = $this->duePlates('vehicle.kir-due');

        $this->assertContains($within->plat_new, $plates);
        $this->assertNotContains($beyond->plat_new, $plates);
        $this->assertContains($overdue->plat_new, $plates);

        $overdueDocument = app(VehicleDocumentMonitorService::class)
            ->dueDocuments()
            ->firstWhere('plate', $overdue->plat_new);

        $this->assertSame('overdue', $overdueDocument['state']);
        $this->assertSame(24, $overdueDocument['days_overdue']);
    }

    public function test_due_documents_can_be_filtered_by_dashboard_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $vehicleA = $this->makeVehicle($companyA, ['stnk_due_date' => $this->inDays(10)]);
        $vehicleB = $this->makeVehicle($companyB, ['stnk_due_date' => $this->inDays(10)]);

        $this->actingAs($this->userWith(['master.vehicles.reminders', 'management.tenant.bypass'], []));

        $plates = app(VehicleDocumentMonitorService::class)
            ->dueDocuments($companyA->id)
            ->pluck('plate')
            ->all();

        $this->assertContains($vehicleA->plat_new, $plates);
        $this->assertNotContains($vehicleB->plat_new, $plates);
    }

    public function test_document_notification_reappears_after_becoming_overdue(): void
    {
        $this->travelTo(now()->startOfDay());

        $company = Company::factory()->create();
        $vehicle = $this->makeVehicle($company, ['stnk_due_date' => $this->inDays(1)]);
        $this->actingAs($this->userWith(['master.vehicles.reminders'], [$company]));

        $upcoming = collect($this->getJson(route('notifications.index'))->assertOk()->json('items'))
            ->firstWhere('title', $vehicle->plat_new);

        $this->assertNotNull($upcoming);
        $this->assertStringContainsString('H-1', $upcoming['subtitle']);

        $this->postJson(route('notifications.read'), ['key' => $upcoming['key']])
            ->assertOk();

        $this->travel(2)->days();

        $overdue = collect($this->getJson(route('notifications.index'))->assertOk()->json('items'))
            ->firstWhere('title', $vehicle->plat_new);

        $this->assertNotNull($overdue);
        $this->assertNotSame($upcoming['key'], $overdue['key']);
        $this->assertStringEndsWith(':overdue', $overdue['key']);
        $this->assertStringContainsString('Lewat 1 hari', $overdue['subtitle']);
        $this->assertStringNotContainsString('H--', $overdue['subtitle']);
    }

    private function inDays(int $days): string
    {
        return now()->startOfDay()->addDays($days)->toDateString();
    }

    private function makeVehicle(Company $company, array $attributes): Vehicle
    {
        return Vehicle::factory()->create(array_merge(
            ['company_id' => $company->id, 'status' => 'ACTIVE'],
            $attributes,
        ));
    }

    /**
     * Plates of the document-due notifications of the given type for the acting user.
     *
     * @return list<string>
     */
    private function duePlates(string $type): array
    {
        return collect($this->getJson(route('notifications.index'))->assertOk()->json('items'))
            ->where('type', $type)
            ->pluck('title')
            ->all();
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
