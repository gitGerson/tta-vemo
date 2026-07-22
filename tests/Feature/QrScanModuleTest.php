<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrScanModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_scan_page_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWith(['operational.qr_scan.view', 'management.tenant.bypass'], []));

        $this->get(route('qr-scan.index'))
            ->assertOk()
            ->assertSee('Scan QR Kendaraan')
            ->assertSee('camera-placeholder-icon')
            ->assertSee("html[data-bs-theme='dark'] .camera-placeholder", false)
            ->assertSee('var(--bs-secondary-bg)', false)
            ->assertDontSee('background: #f4f5fb', false);
    }

    public function test_user_without_permission_cannot_access_scan_page(): void
    {
        $this->actingAs($this->userWith(['master.vehicles.view'], []));

        // CheckPermission redirects browser (GET) requests away; JSON requests get 403.
        $this->get(route('qr-scan.index'))->assertRedirect();
        $this->postJson(route('qr-scan.resolve'), ['barcode' => 'X'])->assertForbidden();
    }

    public function test_resolve_returns_vehicle_with_actions(): void
    {
        $this->actingAs($this->userWith([
            'operational.qr_scan.view',
            'master.vehicles.view',
            'operational.maintenances.create',
            'operational.vehicle_photos.create',
            'management.tenant.bypass',
        ], []));

        $vehicle = Vehicle::factory()->create(['plat_new' => 'H 8888 QR']);

        $this->postJson(route('qr-scan.resolve'), ['barcode' => 'H 8888 QR'])
            ->assertOk()
            ->assertJsonPath('item.identifier', 'H 8888 QR')
            ->assertJsonPath('actions.maintenance.allowed', true)
            ->assertJsonPath('actions.photo.allowed', true)
            ->assertJsonPath('actions.view.url', route('vehicles.show', $vehicle))
            ->assertJsonPath('actions.maintenance.url', route('maintenances.create', ['vehicle' => 'H 8888 QR']))
            ->assertJsonPath('actions.photo.url', route('vehicle-photos.create', ['vehicle_id' => $vehicle->id]));
    }

    public function test_resolve_action_permissions_are_gated(): void
    {
        // Only scan + view; no maintenance/photo create permissions.
        $this->actingAs($this->userWith([
            'operational.qr_scan.view',
            'master.vehicles.view',
            'management.tenant.bypass',
        ], []));

        Vehicle::factory()->create(['plat_new' => 'H 1212 GT']);

        $this->postJson(route('qr-scan.resolve'), ['barcode' => 'H 1212 GT'])
            ->assertOk()
            ->assertJsonPath('actions.view.allowed', true)
            ->assertJsonPath('actions.maintenance.allowed', false)
            ->assertJsonPath('actions.photo.allowed', false);
    }

    public function test_resolve_normalizes_scanned_label_url(): void
    {
        $this->actingAs($this->userWith(['operational.qr_scan.view', 'management.tenant.bypass'], []));

        Vehicle::factory()->create(['plat_new' => 'H 7777 URL']);

        $scanned = route('qr-scan.index', ['barcode' => 'H 7777 URL']);

        $this->postJson(route('qr-scan.resolve'), ['barcode' => $scanned])
            ->assertOk()
            ->assertJsonPath('item.identifier', 'H 7777 URL');
    }

    public function test_resolve_returns_404_for_unknown_plate(): void
    {
        $this->actingAs($this->userWith(['operational.qr_scan.view', 'management.tenant.bypass'], []));

        $this->postJson(route('qr-scan.resolve'), ['barcode' => 'NOPE 0000'])
            ->assertNotFound();
    }

    public function test_resolve_is_company_scoped(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        // Vehicle belongs to company B.
        Vehicle::factory()->create(['company_id' => $companyB->id, 'plat_new' => 'H 5555 TEN']);

        // User belongs only to company A and has no tenant bypass.
        $this->actingAs($this->userWith(['operational.qr_scan.view'], [$companyA]));

        $this->postJson(route('qr-scan.resolve'), ['barcode' => 'H 5555 TEN'])
            ->assertNotFound();
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
            'display_name' => 'QR Scan Test Role',
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
