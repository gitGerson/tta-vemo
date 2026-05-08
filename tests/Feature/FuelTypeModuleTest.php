<?php

namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelTypeModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fuel_type_data_endpoint_returns_rows_for_user_with_view_permission(): void
    {
        $permission = Permission::create([
            'name' => 'master.fuel_types.view',
            'display_name' => 'Fuel Types - View',
            'description' => 'Can view fuel types',
            'module' => 'master',
        ]);

        $role = Role::create([
            'name' => 'fuel_type_viewer',
            'display_name' => 'Fuel Type Viewer',
            'description' => 'Can view fuel type module',
        ]);

        $role->assignPermission($permission);

        $user = User::factory()->create([
            'username' => 'fueltypeviewer',
        ]);
        $user->assignRole($role);

        FuelType::create([
            'name' => 'Solar',
            'price' => 6800,
        ]);

        $response = $this->actingAs($user)->get(route('fuel-type.data'));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Solar',
        ]);
    }
}
