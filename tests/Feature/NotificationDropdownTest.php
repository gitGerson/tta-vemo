<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_endpoint_returns_seven_unread_items_per_page(): void
    {
        $this->actingAs($this->userWithMaintenancePermissions());

        for ($index = 1; $index <= 9; $index++) {
            $this->createAwaitingConfirmationNotification($index);
        }

        $this->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('count', 9)
            ->assertJsonPath('offset', 0)
            ->assertJsonPath('limit', 7)
            ->assertJsonPath('next_offset', 7)
            ->assertJsonPath('has_more', true)
            ->assertJsonCount(7, 'items')
            ->assertJsonStructure(['items' => [['key', 'type', 'title', 'subtitle', 'link']]]);

        $this->getJson(route('notifications.index', ['offset' => 7]))
            ->assertOk()
            ->assertJsonPath('count', 9)
            ->assertJsonPath('offset', 7)
            ->assertJsonPath('limit', 7)
            ->assertJsonPath('next_offset', 14)
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
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'maintenance-notification-viewer-'.fake()->unique()->numerify('###'),
            'display_name' => 'Maintenance Notification Viewer',
        ]);

        collect([
            'operational.maintenances.view',
            'operational.maintenances.approve',
            'management.tenant.bypass',
        ])->each(function (string $name) use ($role): void {
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
