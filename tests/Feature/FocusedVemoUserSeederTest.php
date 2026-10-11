<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\FocusedVemoUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FocusedVemoUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_three_local_users_with_exactly_one_matching_role(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(RolePermissionSeeder::class);
        $this->seed(FocusedVemoUserSeeder::class);

        foreach ([
            'admin@test.local' => 'Admin',
            'kasir@test.local' => 'Kasir',
            'spv@test.local' => 'SPV',
        ] as $email => $roleName) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame($email, $user->username);
            $this->assertEqualsCanonicalizing([$roleName], $user->roles->pluck('name')->all());
            $this->assertTrue(Hash::check(env('VEMO_DEMO_PASSWORD', 'VemoLocal123!'), $user->password));
        }
    }

    public function test_repeated_seeding_does_not_duplicate_users_or_assignments(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(RolePermissionSeeder::class);
        $this->seed(FocusedVemoUserSeeder::class);
        $ids = User::whereIn('email', ['admin@test.local', 'kasir@test.local', 'spv@test.local'])
            ->pluck('id', 'email')->all();

        $this->seed(FocusedVemoUserSeeder::class);

        $this->assertSame($ids, User::whereIn('email', array_keys($ids))->pluck('id', 'email')->all());
        foreach (array_keys($ids) as $email) {
            $this->assertCount(1, User::where('email', $email)->firstOrFail()->roles);
        }
    }

    public function test_does_not_create_demo_users_outside_local(): void
    {
        $this->app->instance('env', 'testing');
        $this->seed(FocusedVemoUserSeeder::class);

        foreach (['admin@test.local', 'kasir@test.local', 'spv@test.local'] as $email) {
            $this->assertDatabaseMissing('users', ['email' => $email]);
        }
    }
}
