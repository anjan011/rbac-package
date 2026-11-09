<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run the setup command which seeds initial data
        $this->artisan('anjan011-roles-permissions:setup');
    }

    public function test_system_role_permissions_are_not_seeded()
    {
        $systemRolePermissions = [
            'system-role.view',
            'system-role.create',
            'system-role.update',
            'system-role.delete',
            'system-role.import',
            'system-role.export',
        ];

        foreach ($systemRolePermissions as $permissionName) {
            $this->assertDatabaseMissing(config('anjan001.roles-and-permissions.permissions_table'), [
                'name' => $permissionName,
            ]);
        }
    }

    public function test_admin_role_does_not_have_system_role_permissions()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $this->assertNotNull($adminRole);

        $this->assertFalse($adminRole->permissions()->where('name', 'system-role.view')->exists());
    }

    public function test_only_super_admin_can_access_system_roles_index()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->addRoleById(1); // Super Admin

        $regularUser = User::factory()->create();
        // Regular user might have Admin role or some permissions, but NOT Super Admin
        $adminRole = Role::where('name', 'Admin')->first();
        $regularUser->roles()->attach($adminRole->getKey());

        $this->actingAs($superAdmin);
        $response = $this->getJson('/api/anjan011/roles-and-permissions/system-roles');
        $response->assertStatus(200);

        $this->actingAs($regularUser);
        $response = $this->getJson('/api/anjan011/roles-and-permissions/system-roles');
        $response->assertStatus(403);
    }

    public function test_only_super_admin_can_create_system_role()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->addRoleById(1);

        $regularUser = User::factory()->create();
        $adminRole = Role::where('name', 'Admin')->first();
        $regularUser->addRoleById($adminRole->getKey()); // Has custom-role.create

        $this->actingAs($superAdmin);
        $response = $this->postJson('/api/anjan011/roles-and-permissions/system-roles', [
            'name' => 'New System Role Test',
        ]);
        $response->assertStatus(201);

        $this->actingAs($regularUser);
        $response = $this->postJson('/api/anjan011/roles-and-permissions/system-roles', [
            'name' => 'Another System Role',
        ]);
        $response->assertStatus(403);
    }

    public function test_custom_role_management_still_requires_permissions()
    {
        $userWithPermission = User::factory()->create();
        $adminRole = Role::where('name', 'Admin')->first();
        $userWithPermission->addRoleById($adminRole->getKey());

        $userWithoutPermission = User::factory()->create();

        $this->actingAs($userWithPermission);
        $response = $this->getJson('/api/anjan011/roles-and-permissions/custom-roles');
        $response->assertStatus(200);

        $this->actingAs($userWithoutPermission);
        $response = $this->getJson('/api/anjan011/roles-and-permissions/custom-roles');
        $response->assertStatus(403);
    }
}
