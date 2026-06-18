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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_updates_links_the_user_owning_the_token(): void
    {
        config(['services.telegram-bot-api.token' => 'bot-token']);
        Http::fake([
            '*/getUpdates*' => Http::response([
                'ok' => true,
                'result' => [[
                    'update_id' => 5001,
                    'message' => ['text' => '/start tok123', 'chat' => ['id' => 99887766]],
                ]],
            ]),
            '*' => Http::response(['ok' => true]),
        ]);

        $user = User::factory()->create(['telegram_link_token' => 'tok123']);

        $this->artisan('telegram:poll-updates --once')->assertSuccessful();

        $user->refresh();
        $this->assertSame('99887766', $user->telegram_chat_id);
        $this->assertNull($user->telegram_link_token);
        $this->assertNotNull($user->telegram_linked_at);

        // Offset advances past the processed update so it isn't re-fetched.
        $this->assertSame(5002, (int) Cache::get('telegram:poll:offset'));
    }

    public function test_unknown_token_is_ignored_and_replied_to(): void
    {
        config(['services.telegram-bot-api.token' => 'bot-token']);
        Http::fake([
            '*/getUpdates*' => Http::response([
                'ok' => true,
                'result' => [[
                    'update_id' => 6001,
                    'message' => ['text' => '/start nope', 'chat' => ['id' => 42]],
                ]],
            ]),
            '*' => Http::response(['ok' => true]),
        ]);

        $user = User::factory()->create(['telegram_link_token' => 'real-token']);

        $this->artisan('telegram:poll-updates --once')->assertSuccessful();

        $user->refresh();
        $this->assertNull($user->telegram_chat_id);
        $this->assertSame('real-token', $user->telegram_link_token);

        // The bot replies that the link is invalid.
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/sendMessage')
            && $request['chat_id'] === 42);
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
