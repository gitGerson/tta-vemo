<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TelegramNotificationLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use App\Notifications\ReminderTelegramNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_command_links_the_user_owning_the_token(): void
    {
        config(['services.telegram_bot.webhook_secret' => 'secret']);
        Http::fake();

        $user = User::factory()->create(['telegram_link_token' => 'tok123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'secret')
            ->postJson(route('telegram.webhook'), [
                'message' => ['text' => '/start tok123', 'chat' => ['id' => 99887766]],
            ])
            ->assertOk();

        $user->refresh();
        $this->assertSame('99887766', $user->telegram_chat_id);
        $this->assertNull($user->telegram_link_token);
        $this->assertNotNull($user->telegram_linked_at);
    }

    public function test_webhook_rejects_wrong_secret(): void
    {
        config(['services.telegram_bot.webhook_secret' => 'secret']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'wrong')
            ->postJson(route('telegram.webhook'), [
                'message' => ['text' => '/start tok', 'chat' => ['id' => 1]],
            ])
            ->assertForbidden();
    }

    public function test_dispatch_sends_due_reminder_once_and_records_ledger(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $user = $this->linkedUserWith(['master.vehicle_insurances.reminders'], [$company]);
        $this->makeExpiringInsurance($company, 10);

        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertSentTo($user, ReminderTelegramNotification::class);
        $this->assertSame(1, TelegramNotificationLog::where('user_id', $user->id)->count());

        // A second run must not resend the same reminder.
        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertSentToTimes($user, ReminderTelegramNotification::class, 1);
        $this->assertSame(1, TelegramNotificationLog::where('user_id', $user->id)->count());
    }

    public function test_dispatch_skips_users_without_linked_telegram(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        // Permission + due policy, but no telegram_chat_id.
        $user = $this->userWith(['master.vehicle_insurances.reminders'], [$company]);
        $this->makeExpiringInsurance($company, 10);

        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertNothingSentTo($user);
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
    private function linkedUserWith(array $permissions, array $companies): User
    {
        $user = $this->userWith($permissions, $companies);
        $user->forceFill(['telegram_chat_id' => '12345', 'telegram_linked_at' => now()])->save();

        return $user;
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
            'display_name' => 'Telegram Test Role',
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
