<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_can_create_update_and_delete_roles()
    {
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        // Custom Role
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Test Role',
            'type' => 'Custom',
            'description' => 'Test Description',
            'is_enabled' => 1,
        ]);

        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.roles_table'), [
            'name' => 'Test Role',
            'type' => 'Custom',
        ]);

        $role->update(['description' => 'Updated Description']);
        $this->assertEquals('Updated Description', $role->fresh()->description);

        $role->delete();
        $this->assertDatabaseMissing(config('anjan001.roles-and-permissions.roles_table'), [
            'name' => 'Test Role',
        ]);

        // System Role
        $systemRole = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'System Admin',
            'type' => 'System',
            'description' => 'System Role',
            'is_enabled' => 1,
        ]);

        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.roles_table'), [
            'name' => 'System Admin',
            'type' => 'System',
        ]);
    }

    public function test_it_cannot_modify_system_roles()
    {
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'System Role Protection',
            'type' => 'System',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('System roles cannot be modified.');
        $role->update(['description' => 'Try to update']);
    }

    public function test_it_cannot_delete_system_roles()
    {
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'System Role Delete Protection',
            'type' => 'System',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('System roles cannot be deleted.');
        $role->delete();
    }

    public function test_it_can_create_update_and_delete_permissions()
    {
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'test_permission',
            'type' => 'Custom',
            'description' => 'Test Permission Description',
            'is_enabled' => 1,
        ]);

        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.permissions_table'), [
            'name' => 'test_permission',
            'type' => 'Custom',
        ]);

        $permission->update(['description' => 'Updated Permission Description']);
        $this->assertEquals('Updated Permission Description', $permission->fresh()->description);

        $permission->delete();
        $this->assertDatabaseMissing(config('anjan001.roles-and-permissions.permissions_table'), [
            'name' => 'test_permission',
        ]);
    }

    public function test_it_cannot_modify_system_permissions()
    {
        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'system_permission',
            'type' => 'System',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('System permissions cannot be modified.');
        $permission->update(['description' => 'Try to update']);
    }

    public function test_it_cannot_delete_system_permissions()
    {
        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'system_permission_delete',
            'type' => 'System',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('System permissions cannot be deleted.');
        $permission->delete();
    }

    public function test_it_can_assign_and_remove_permissions_from_roles()
    {
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Role with Permissions',
            'type' => 'Custom',
        ]);

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'role_permission',
        ]);

        $role->permissions()->attach($permission->{config('anjan001.roles-and-permissions.permission_id_column')});

        $this->assertTrue($role->permissions->contains('name', 'role_permission'));

        $role->permissions()->detach($permission->{config('anjan001.roles-and-permissions.permission_id_column')});
        $this->assertFalse($role->fresh()->permissions->contains('name', 'role_permission'));
    }

    public function test_it_can_assign_and_remove_roles_from_users()
    {
        $user = User::factory()->create();
        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'User Role',
            'type' => 'Custom',
        ]);

        $user->addRoleById($role->{config('anjan001.roles-and-permissions.role_id_column')});
        $this->assertTrue($user->hasRoleByName('User Role'));

        $user->removeRoleByName('User Role');
        $this->assertFalse($user->hasRoleByName('User Role'));
    }
}
