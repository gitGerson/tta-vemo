<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_endpoint_pages_unread_items(): void
    {
        $this->actingAs($this->userWithMaintenancePermissions());

        $pageSize = NotificationService::PAGE_SIZE;
        $total = $pageSize + 2;

        for ($index = 1; $index <= $total; $index++) {
            $this->createAwaitingConfirmationNotification($index);
        }

        $this->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('count', $total)
            ->assertJsonPath('offset', 0)
            ->assertJsonPath('limit', $pageSize)
            ->assertJsonPath('next_offset', $pageSize)
            ->assertJsonPath('has_more', true)
            ->assertJsonCount($pageSize, 'items')
            ->assertJsonStructure(['items' => [['key', 'type', 'title', 'subtitle', 'link']]]);

        $this->getJson(route('notifications.index', ['offset' => $pageSize]))
            ->assertOk()
            ->assertJsonPath('count', $total)
            ->assertJsonPath('offset', $pageSize)
            ->assertJsonPath('limit', $pageSize)
            ->assertJsonPath('next_offset', $pageSize * 2)
            ->assertJsonPath('has_more', false)
            ->assertJsonCount(2, 'items');
    }

    public function test_read_endpoint_creates_receipt_and_hides_notification_for_that_user(): void
    {
        $user = $this->userWithMaintenancePermissions();
        $this->actingAs($user);
        $this->createAwaitingConfirmationNotification();

        $key = $this->getJson(route('notifications.index'))->json('items.0.key');

        $this->postJson(route('notifications.read'), ['key' => $key])
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertDatabaseHas('notification_read_receipts', [
            'user_id' => $user->id,
            'notification_key' => $key,
            'notification_type' => 'maintenance.awaiting-confirmation',
        ]);

        $this->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonCount(0, 'items');
    }

    public function test_read_receipts_are_scoped_per_user(): void
    {
        $firstUser = $this->userWithMaintenancePermissions();
        $secondUser = $this->userWithMaintenancePermissions();
        $this->createAwaitingConfirmationNotification();

        $this->actingAs($firstUser);
        $key = $this->getJson(route('notifications.index'))->json('items.0.key');
        $this->postJson(route('notifications.read'), ['key' => $key])->assertOk();

        $this->actingAs($secondUser);

        $this->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.key', $key);
    }

    public function test_changed_condition_key_reappears_after_old_key_was_read(): void
    {
        $user = $this->userWithMaintenancePermissions();
        $this->actingAs($user);
        $maintenance = $this->createAwaitingConfirmationNotification();

        $oldKey = $this->getJson(route('notifications.index'))->json('items.0.key');
        $this->postJson(route('notifications.read'), ['key' => $oldKey])->assertOk();

        $maintenance->forceFill(['updated_at' => now()->addMinute()])->saveQuietly();

        $this->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.key', fn (string $key): bool => $key !== $oldKey);
    }

    public function test_group_filter_returns_only_that_groups_types(): void
    {
        $this->actingAs($this->userWithGroupedReminderPermissions());
        $this->createAwaitingConfirmationNotification();
        $this->createStnkDueNotification();

        $this->getJson(route('notifications.index', ['group' => 'dokumen']))
            ->assertOk()
            ->assertJsonPath('group', 'dokumen')
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.type', 'vehicle.stnk-due');

        $this->getJson(route('notifications.index', ['group' => 'perbaikan']))
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.type', 'maintenance.awaiting-confirmation');
    }

    public function test_group_counts_and_total_count_cover_the_whole_unread_set(): void
    {
        $this->actingAs($this->userWithGroupedReminderPermissions());

        $pageSize = NotificationService::PAGE_SIZE;
        $documents = $pageSize + 1;

        for ($index = 1; $index <= $documents; $index++) {
            $this->createStnkDueNotification($index);
        }

        $this->createAwaitingConfirmationNotification();

        // count stays the all-groups total so the bell badge doesn't shrink, and
        // the per-tab counts describe the full set rather than the current page.
        $this->getJson(route('notifications.index', ['group' => 'dokumen']))
            ->assertOk()
            ->assertJsonPath('count', $documents + 1)
            ->assertJsonCount($pageSize, 'items')
            ->assertJsonPath('groups.dokumen', $documents)
            ->assertJsonPath('groups.perbaikan', 1)
            ->assertJsonPath('groups.asuransi', 0)
            ->assertJsonPath('groups.foto', 0);
    }

    public function test_paging_within_a_group_ignores_other_groups(): void
    {
        $this->actingAs($this->userWithGroupedReminderPermissions());

        $pageSize = NotificationService::PAGE_SIZE;

        for ($index = 1; $index <= $pageSize + 1; $index++) {
            $this->createStnkDueNotification($index);
        }

        $this->createAwaitingConfirmationNotification();

        $this->getJson(route('notifications.index', ['group' => 'dokumen']))
            ->assertOk()
            ->assertJsonPath('has_more', true);

        // Only the last document reminder is left — the maintenance item belongs to
        // another group and must not spill into this page.
        $this->getJson(route('notifications.index', ['group' => 'dokumen', 'offset' => $pageSize]))
            ->assertOk()
            ->assertJsonPath('has_more', false)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.type', 'vehicle.stnk-due');
    }

    public function test_unknown_group_falls_back_to_unfiltered(): void
    {
        $this->actingAs($this->userWithGroupedReminderPermissions());
        $this->createAwaitingConfirmationNotification();
        $this->createStnkDueNotification();

        $this->getJson(route('notifications.index', ['group' => 'tidak-ada']))
            ->assertOk()
            ->assertJsonPath('group', null)
            ->assertJsonCount(2, 'items');
    }

    public function test_offcanvas_renders_a_tab_per_group(): void
    {
        $this->actingAs($this->userWithGroupedReminderPermissions());
        $this->createStnkDueNotification();

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('id="notifications-offcanvas"', false);
        $response->assertSee('data-notifications-dropdown', false);
        $response->assertSee('data-notifications-toggle', false);
        $response->assertSee('data-notifications-tabs', false);
        $response->assertSee('data-notifications-tab=""', false);

        foreach (array_keys(NotificationService::GROUPS) as $group) {
            $response->assertSee('data-notifications-tab="'.$group.'"', false);
            $response->assertSee('data-notifications-pane="'.$group.'"', false);
        }
    }

    public function test_offcanvas_never_uses_the_bootstrap_data_api(): void
    {
        $this->actingAs($this->userWithGroupedReminderPermissions());

        // Both loaded Bootstrap bundles register the offcanvas data-api, so a
        // declarative trigger would open two instances and leave a stuck backdrop.
        // navbar-notifications.js drives a single instance instead.
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-bs-toggle="offcanvas"', false)
            ->assertDontSee('data-bs-dismiss="offcanvas"', false);
    }

    private function createStnkDueNotification(int $index = 1): Vehicle
    {
        return Vehicle::factory()->create([
            'plat_new' => sprintf('S %04d TK', $index),
            'stnk_due_date' => now()->addDays(10)->toDateString(),
        ]);
    }

    private function createAwaitingConfirmationNotification(int $index = 1): Maintenance
    {
        $vehicle = Vehicle::factory()->create([
            'plat_new' => sprintf('H %04d NT', $index),
        ]);

        return Maintenance::create([
            'code_mt' => sprintf('MT-NOTIF-%02d-%s', $index, fake()->unique()->numerify('###')),
            'no_pol' => $vehicle->plat_new,
            'date_in' => now()->toDateString(),
            'status' => Maintenance::STATUS_COMPLETED,
            'is_confirmed' => false,
            'updated_at' => now()->subSeconds($index),
        ]);
    }

    private function userWithMaintenancePermissions(): User
    {
        return $this->userWithPermissions([
            'operational.maintenances.view',
            'operational.maintenances.approve',
            'management.tenant.bypass',
        ]);
    }

    /**
     * Maintenance + document reminders only. The photo and insurance reminder
     * permissions are deliberately withheld: vehicle.photo-missing fires for every
     * vehicle without a complete monthly photo set, which would add one item per
     * fixture vehicle and obscure what these tests are measuring. It also leaves
     * the asuransi/foto groups legitimately empty.
     */
    private function userWithGroupedReminderPermissions(): User
    {
        return $this->userWithPermissions([
            'operational.maintenances.view',
            'operational.maintenances.approve',
            'master.vehicles.reminders',
            'management.tenant.bypass',
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'maintenance-notification-viewer-'.fake()->unique()->numerify('###'),
            'display_name' => 'Maintenance Notification Viewer',
        ]);

        collect($permissions)->each(function (string $name) use ($role): void {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $name,
                    'module' => 'operational',
                ],
            );

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
