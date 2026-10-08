<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VemoDummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('VemoDummyUserSeeder hanya boleh dijalankan di environment local.');

            return;
        }

        DB::transaction(function (): void {
            $this->call(VemoRoleSeeder::class);

            $users = [
                'kasir@test.local' => 'Kasir',
                'maintenance@test.local' => 'Admin Maintenance',
                'manager@test.local' => 'Manager',
                'supervisor@test.local' => 'Supervisor',
            ];

            foreach ($users as $email => $roleName) {
                $user = User::withTrashed()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $roleName,
                        'username' => $email,
                        'password' => Hash::make('P@ssw0rd1938'),
                    ],
                );

                if ($user->trashed()) {
                    $user->restore();
                }

                $user->assignRole(Role::where('name', $roleName)->firstOrFail());
            }
        });
    }
}
