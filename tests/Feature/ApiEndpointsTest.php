<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createSuperAdmin()
    {
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);

        $role = Role::where($roleIdCol, $superAdminRoleId)->first();

        if (! $role) {
            $role = Role::create([
                $roleIdCol => $superAdminRoleId,
                'role_uid' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'type' => 'System',
                'is_enabled' => 1,
            ]);
        }

        $user = User::factory()->create();
        $user->addRoleById($superAdminRoleId);

        return $user;
    }

    public function test_super_admin_can_list_system_roles()
    {
        $user = $this->createSuperAdmin();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/anjan011/roles-and-permissions/system-roles');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Super Admin']);
    }

    public function test_non_super_admin_cannot_list_system_roles()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/anjan011/roles-and-permissions/system-roles');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_system_role()
    {
        $user = $this->createSuperAdmin();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/anjan011/roles-and-permissions/system-roles', [
            'name' => 'New System Role',
            'is_enabled' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New System Role', 'type' => 'System']);
    }

    public function test_user_with_permission_can_list_custom_roles()
    {
        Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'custom-role.view',
            'type' => 'System',
            'is_enabled' => 1,
        ]);

        Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Custom Editor',
            'type' => 'Custom',
            'is_enabled' => 1,
        ]);

        $user = User::factory()->create();
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Custom Role Viewer',
            'type' => 'Custom',
            'is_enabled' => 1,
        ]);
        $role->permissions()->attach(Permission::where('name', 'custom-role.view')->first()->permission_id);
        $user->addRoleByName('Custom Role Viewer');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/anjan011/roles-and-permissions/custom-roles');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Custom Editor']);
    }

    public function test_user_without_permission_cannot_list_custom_roles()
    {
        Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'custom-role.view',
            'type' => 'System',
            'is_enabled' => 1,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/anjan011/roles-and-permissions/custom-roles');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_list_permissions()
    {
        $user = $this->createSuperAdmin();
        Sanctum::actingAs($user);

        Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'test.permission',
            'type' => 'Custom',
            'is_enabled' => 1,
        ]);

        $response = $this->getJson('/api/anjan011/roles-and-permissions/permissions');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'test.permission']);
    }
}
