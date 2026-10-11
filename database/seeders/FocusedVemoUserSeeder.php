<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Local-only accounts for the focused VEMO roles.
 *
 * php artisan db:seed --class=FocusedVemoUserSeeder
 */
class FocusedVemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('FocusedVemoUserSeeder only runs in the local environment.');

            return;
        }

        $this->call(FocusedVemoRoleSeeder::class);

        DB::transaction(function (): void {
            foreach ([
                'admin@test.local' => 'Admin',
                'kasir@test.local' => 'Kasir',
                'spv@test.local' => 'SPV',
            ] as $email => $roleName) {
                $role = Role::where('name', $roleName)->firstOrFail();

                $user = User::withTrashed()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $roleName.' VEMO',
                        'username' => $email,
                        'password' => Hash::make(env('VEMO_DEMO_PASSWORD', 'VemoLocal123!')),
                    ],
                );

                if ($user->trashed()) {
                    $user->restore();
                }

                // Demo users must have exactly one role, including on reruns.
                $user->roles()->sync([
                    $role->id => ['assigned_at' => now()],
                ]);
            }
        });

        $this->command?->info('Local VEMO users created: admin@test.local, kasir@test.local, spv@test.local');
    }
}
