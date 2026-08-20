<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCompanyRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_authorized_user_can_sync_companies_on_user_update(): void
    {
        $this->actingAs($this->userWithUserEditPermission());
        $user = User::factory()->create();
        $companies = Company::factory()->count(2)->create();

        $this->put(route('users.update', $user), [
            'company_ids' => $companies->pluck('id')->all(),
        ])->assertRedirect(route('users.edit', $user));

        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_user', [
                'company_id' => $company->id,
                'user_id' => $user->id,
            ]);
        }
    }

    private function userWithUserEditPermission(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'user-company-manager',
            'display_name' => 'User Company Manager',
        ]);
        $permission = Permission::create([
            'name' => 'management.users.edit',
            'display_name' => 'Users - Edit',
            'module' => 'management',
        ]);

        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
