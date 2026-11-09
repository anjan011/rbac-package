<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use Anjan011\RolesAndPermissions\RolesAndPermissionsManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed some permissions for testing
        Permission::create(['permission_id' => 999, 'permission_uid' => Str::uuid(), 'name' => 'any-permission', 'is_enabled' => 1]);
        Permission::create(['permission_id' => 888, 'permission_uid' => Str::uuid(), 'name' => 'another-permission', 'is_enabled' => 1]);
        Permission::create(['permission_id' => 1, 'permission_uid' => Str::uuid(), 'name' => 'p1', 'is_enabled' => 1]);
        Permission::create(['permission_id' => 2, 'permission_uid' => Str::uuid(), 'name' => 'p2', 'is_enabled' => 1]);
        Permission::create(['permission_id' => 100, 'permission_uid' => Str::uuid(), 'name' => 'some-permission', 'is_enabled' => 1]);

        // Super Admin role with ID 1 is already seeded by migration.
        // We just ensure it exists.
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.roles_table'), [
            config('anjan001.roles-and-permissions.role_id_column') => 1,
            'name' => 'Super Admin',
        ]);
    }

    public function test_super_admin_has_all_permissions_by_name()
    {
        $user = User::factory()->create();
        $user->addRoleById(1);

        $this->assertTrue($user->hasPermissionByName('any-permission'));
        $this->assertTrue(RolesAndPermissionsManager::hasPermissionByName($user->id, 'another-permission'));
    }

    public function test_super_admin_has_all_permissions_by_id()
    {
        $user = User::factory()->create();
        $user->addRoleById(1);

        $this->assertTrue($user->hasPermissionById(999));
        $this->assertTrue(RolesAndPermissionsManager::hasPermissionById($user->id, 888));
    }

    public function test_super_admin_has_any_permission()
    {
        $user = User::factory()->create();
        $user->addRoleById(1);

        $this->assertTrue($user->hasAnyPermissionByName(['p1', 'p2']));
        $this->assertTrue($user->hasAnyPermissionById([1, 2]));
    }

    public function test_super_admin_has_all_permissions()
    {
        $user = User::factory()->create();
        $user->addRoleById(1);

        $this->assertTrue($user->hasAllPermissionsByName(['p1', 'p2']));
        $this->assertTrue($user->hasAllPermissionsById([1, 2]));
    }

    public function test_regular_user_does_not_have_unassigned_permissions()
    {
        $user = User::factory()->create();

        $role = Role::create([
            'role_id' => 2,
            'role_uid' => (string) Str::uuid(),
            'name' => 'Regular User',
            'type' => 'Custom',
            'is_enabled' => 1,
        ]);

        $user->addRoleById(2);

        $this->assertFalse($user->hasPermissionByName('some-permission'));
    }

    public function test_custom_super_admin_role_id_override()
    {
        config(['anjan001.roles-and-permissions.super_admin_role_id' => 99]);

        $user = User::factory()->create();

        // Create role with ID 99
        Role::create([
            'role_id' => 99,
            'role_uid' => (string) Str::uuid(),
            'name' => 'Custom Super Admin',
            'type' => 'System',
            'is_enabled' => 1,
        ]);

        $user->addRoleById(99);

        $this->assertTrue($user->hasPermissionByName('any-permission'));

        // A user with role ID 1 should NOT have the override now
        $user2 = User::factory()->create();
        $user2->addRoleById(1);
        $this->assertFalse($user2->hasPermissionByName('any-permission'));
    }
}
