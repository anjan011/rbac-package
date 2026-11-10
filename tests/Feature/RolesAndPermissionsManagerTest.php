<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use Anjan011\RolesAndPermissions\RolesAndPermissionsManager;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesAndPermissionsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected $userId;
    protected $roleIdCol;
    protected $permIdCol;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $this->permIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        // Create a user directly via DB to ensure no reliance on User model trait
        $usersTable = config('anjan001.roles-and-permissions.users_table_name', 'users');
        $this->userId = DB::table($usersTable)->insertGetId([
            'name' => 'Manager Test User',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_has_role_by_id_and_name()
    {
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Editor',
            'is_enabled' => 1
        ]);

        $this->assertFalse(RolesAndPermissionsManager::hasRoleById($this->userId, $role->{$this->roleIdCol}));
        $this->assertFalse(RolesAndPermissionsManager::hasRoleByName($this->userId, 'Editor'));

        RolesAndPermissionsManager::addRoleById($this->userId, $role->{$this->roleIdCol});

        $this->assertTrue(RolesAndPermissionsManager::hasRoleById($this->userId, $role->{$this->roleIdCol}));
        $this->assertTrue(RolesAndPermissionsManager::hasRoleByName($this->userId, 'Editor'));
    }

    public function test_has_any_role()
    {
        $role1 = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Role 1', 'is_enabled' => 1]);
        $role2 = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Role 2', 'is_enabled' => 1]);

        $this->assertFalse(RolesAndPermissionsManager::hasAnyRoleById($this->userId, [$role1->{$this->roleIdCol}, $role2->{$this->roleIdCol}]));

        RolesAndPermissionsManager::addRoleByName($this->userId, 'Role 1');

        $this->assertTrue(RolesAndPermissionsManager::hasAnyRoleById($this->userId, [$role1->{$this->roleIdCol}, $role2->{$this->roleIdCol}]));
        $this->assertTrue(RolesAndPermissionsManager::hasAnyRoleByName($this->userId, ['Role 1', 'Role 2']));
    }

    public function test_has_all_roles()
    {
        $role1 = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Role A', 'is_enabled' => 1]);
        $role2 = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Role B', 'is_enabled' => 1]);

        RolesAndPermissionsManager::addRoleByName($this->userId, 'Role A');

        $this->assertFalse(RolesAndPermissionsManager::hasAllRolesByName($this->userId, ['Role A', 'Role B']));

        RolesAndPermissionsManager::addRoleByName($this->userId, 'Role B');

        $this->assertTrue(RolesAndPermissionsManager::hasAllRolesByName($this->userId, ['Role A', 'Role B']));
        $this->assertTrue(RolesAndPermissionsManager::hasAllRolesById($this->userId, [$role1->{$this->roleIdCol}, $role2->{$this->roleIdCol}]));
    }

    public function test_has_permission_by_id_and_name()
    {
        $role = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Perm Role', 'is_enabled' => 1]);
        $permission = Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'edit-article', 'is_enabled' => 1]);

        $role->permissions()->attach($permission->{$this->permIdCol});

        $this->assertFalse(RolesAndPermissionsManager::hasPermissionByName($this->userId, 'edit-article'));

        RolesAndPermissionsManager::addRoleById($this->userId, $role->{$this->roleIdCol});

        $this->assertTrue(RolesAndPermissionsManager::hasPermissionByName($this->userId, 'edit-article'));
        $this->assertTrue(RolesAndPermissionsManager::hasPermissionById($this->userId, $permission->{$this->permIdCol}));
    }

    public function test_has_any_permission()
    {
        $role = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Multi Perm Role', 'is_enabled' => 1]);
        $p1 = Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'p1', 'is_enabled' => 1]);
        $p2 = Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'p2', 'is_enabled' => 1]);

        $role->permissions()->attach($p1->{$this->permIdCol});
        RolesAndPermissionsManager::addRoleById($this->userId, $role->{$this->roleIdCol});

        $this->assertTrue(RolesAndPermissionsManager::hasAnyPermissionByName($this->userId, ['p1', 'p2']));
        $this->assertTrue(RolesAndPermissionsManager::hasAnyPermissionById($this->userId, [$p1->{$this->permIdCol}, $p2->{$this->permIdCol}]));
        $this->assertFalse(RolesAndPermissionsManager::hasAnyPermissionByName($this->userId, ['p2']));
    }

    public function test_has_all_permissions()
    {
        $role = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'All Perm Role', 'is_enabled' => 1]);
        $p1 = Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'a1', 'is_enabled' => 1]);
        $p2 = Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'a2', 'is_enabled' => 1]);

        $role->permissions()->attach($p1->{$this->permIdCol});
        RolesAndPermissionsManager::addRoleById($this->userId, $role->{$this->roleIdCol});

        $this->assertFalse(RolesAndPermissionsManager::hasAllPermissionsByName($this->userId, ['a1', 'a2']));

        $role->permissions()->attach($p2->{$this->permIdCol});

        $this->assertTrue(RolesAndPermissionsManager::hasAllPermissionsByName($this->userId, ['a1', 'a2']));
        $this->assertTrue(RolesAndPermissionsManager::hasAllPermissionsById($this->userId, [$p1->{$this->permIdCol}, $p2->{$this->permIdCol}]));
    }

    public function test_super_admin_override()
    {
        $saRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        
        // Ensure Super Admin role exists (usually seeded by migration)
        if (! Role::where($this->roleIdCol, $saRoleId)->exists()) {
            Role::create([
                $this->roleIdCol => $saRoleId,
                'role_uid' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'type' => 'System',
                'is_enabled' => 1,
            ]);
        }

        $permission = Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'secret-action', 'is_enabled' => 1]);

        $this->assertFalse(RolesAndPermissionsManager::hasPermissionByName($this->userId, 'secret-action'));

        RolesAndPermissionsManager::addRoleById($this->userId, $saRoleId);

        $this->assertTrue(RolesAndPermissionsManager::hasPermissionByName($this->userId, 'secret-action'));
    }

    public function test_remove_role()
    {
        $role = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'To Remove', 'is_enabled' => 1]);
        RolesAndPermissionsManager::addRoleByName($this->userId, 'To Remove');
        $this->assertTrue(RolesAndPermissionsManager::hasRoleByName($this->userId, 'To Remove'));

        RolesAndPermissionsManager::removeRoleByName($this->userId, 'To Remove');
        $this->assertFalse(RolesAndPermissionsManager::hasRoleByName($this->userId, 'To Remove'));

        RolesAndPermissionsManager::addRoleById($this->userId, $role->{$this->roleIdCol});
        $this->assertTrue(RolesAndPermissionsManager::hasRoleById($this->userId, $role->{$this->roleIdCol}));

        RolesAndPermissionsManager::removeRoleById($this->userId, $role->{$this->roleIdCol});
        $this->assertFalse(RolesAndPermissionsManager::hasRoleById($this->userId, $role->{$this->roleIdCol}));
    }

    public function test_list_methods()
    {
        $r1 = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'L1', 'is_enabled' => 1]);
        $r2 = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'L2', 'is_enabled' => 1]);

        RolesAndPermissionsManager::addRolesByNameList($this->userId, ['L1', 'L2']);
        $this->assertTrue(RolesAndPermissionsManager::hasAllRolesByName($this->userId, ['L1', 'L2']));

        RolesAndPermissionsManager::removeRolesByNameList($this->userId, ['L1']);
        $this->assertFalse(RolesAndPermissionsManager::hasRoleByName($this->userId, 'L1'));
        $this->assertTrue(RolesAndPermissionsManager::hasRoleByName($this->userId, 'L2'));

        RolesAndPermissionsManager::removeRolesByIdList($this->userId, [$r2->{$this->roleIdCol}]);
        $this->assertFalse(RolesAndPermissionsManager::hasRoleByName($this->userId, 'L2'));
    }

    public function test_validation_exceptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid role');
        RolesAndPermissionsManager::hasRoleByName($this->userId, 'Non Existent');
    }
}
