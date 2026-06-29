<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\EmailNotificationLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use App\Notifications\ReminderEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_sends_email_digest_once_and_records_ledger(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $user = $this->userWith(['master.vehicle_insurances.reminders'], [$company]);
        $this->makeExpiringInsurance($company, 10);

        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertSentTo($user, ReminderEmailNotification::class);
        $this->assertSame(1, EmailNotificationLog::where('user_id', $user->id)->count());

        // A second run must not resend the same reminder.
        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertSentToTimes($user, ReminderEmailNotification::class, 1);
        $this->assertSame(1, EmailNotificationLog::where('user_id', $user->id)->count());
    }

    public function test_dispatch_skips_email_for_users_without_an_email(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $user = $this->userWith(['master.vehicle_insurances.reminders'], [$company]);
        $user->forceFill(['email' => null])->save();
        $this->makeExpiringInsurance($company, 10);

        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertNotSentTo($user, ReminderEmailNotification::class);
        $this->assertSame(0, EmailNotificationLog::where('user_id', $user->id)->count());
    }

    public function test_dispatch_skips_email_for_users_without_pending_items(): void
    {
        Notification::fake();

        // Has an email but no permission, so no reminder items are produced.
        $user = User::factory()->create();

        $this->artisan('telegram:dispatch-reminders')->assertSuccessful();

        Notification::assertNotSentTo($user, ReminderEmailNotification::class);
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
            'display_name' => 'Email Test Role',
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
