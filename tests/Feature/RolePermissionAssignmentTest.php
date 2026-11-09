<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RolePermissionAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('anjan011-roles-permissions:setup');
    }

    protected function getSuperAdmin()
    {
        $user = User::factory()->create();
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        $user->addRoleById($superAdminRoleId);

        return $user;
    }

    public function test_custom_role_create_with_permission_ids()
    {
        $user = User::factory()->create();
        // Give permission to create custom roles
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'test.perm',
            'is_enabled' => 1,
        ]);
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $response = $this->postJson('/api/anjan011/roles-and-permissions/custom-roles', [
            'name' => 'New Custom Role',
            'permission_ids' => [$permission->$permissionIdCol],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $permission->$permissionIdCol,
            'role_id' => $response->json('data.role_id'),
        ]);
    }

    public function test_custom_role_create_with_permission_names()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'test.perm.name',
            'is_enabled' => 1,
        ]);
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $response = $this->postJson('/api/anjan011/roles-and-permissions/custom-roles', [
            'name' => 'New Custom Role 2',
            'permission_names' => ['test.perm.name'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $permission->$permissionIdCol,
            'role_id' => $response->json('data.role_id'),
        ]);
    }

    public function test_custom_role_create_precedence_ids_over_names()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $perm1 = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'perm1',
            'is_enabled' => 1,
        ]);
        $perm2 = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'perm2',
            'is_enabled' => 1,
        ]);
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $response = $this->postJson('/api/anjan011/roles-and-permissions/custom-roles', [
            'name' => 'New Custom Role 3',
            'permission_ids' => [$perm1->$permissionIdCol],
            'permission_names' => ['perm2'],
        ]);

        $response->assertStatus(201);
        // Should only have perm1
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $perm1->$permissionIdCol,
            'role_id' => $response->json('data.role_id'),
        ]);
        $this->assertDatabaseMissing(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $perm2->$permissionIdCol,
            'role_id' => $response->json('data.role_id'),
        ]);
    }

    public function test_custom_role_create_fails_with_non_integer_ids()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $response = $this->postJson('/api/anjan011/roles-and-permissions/custom-roles', [
            'name' => 'Fail Role',
            'permission_ids' => ['not-an-integer'],
        ]);

        $response->assertStatus(400);
    }

    public function test_custom_role_create_fails_with_non_string_names()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $response = $this->postJson('/api/anjan011/roles-and-permissions/custom-roles', [
            'name' => 'Fail Role 2',
            'permission_names' => [123],
        ]);

        $response->assertStatus(400);
    }

    public function test_custom_role_create_fails_with_non_existent_id()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $response = $this->postJson('/api/anjan011/roles-and-permissions/custom-roles', [
            'name' => 'Fail Role 3',
            'permission_ids' => [9999],
        ]);

        // Should throw exception, and catch it in controller returning 422 or similar
        // The prompt says "throw an exception".
        $response->assertStatus(422);
        $this->assertEquals('Invalid permission', $response->json('message'));
    }

    public function test_system_role_create_with_permission_ids()
    {
        $this->actingAs($this->getSuperAdmin());

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'sys.perm',
            'is_enabled' => 1,
        ]);
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $response = $this->postJson('/api/anjan011/roles-and-permissions/system-roles', [
            'name' => 'New System Role',
            'permission_ids' => [$permission->$permissionIdCol],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $permission->$permissionIdCol,
            'role_id' => $response->json('data.role_id'),
        ]);
    }

    public function test_custom_role_update_with_permission_ids()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Role to Update',
            'type' => 'Custom',
            'is_enabled' => 1,
        ]);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'update.perm',
            'is_enabled' => 1,
        ]);
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $response = $this->putJson('/api/anjan011/roles-and-permissions/custom-roles/'.$role->$roleIdCol, [
            'permission_ids' => [$permission->$permissionIdCol],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $permission->$permissionIdCol,
            'role_id' => $role->$roleIdCol,
        ]);
    }

    public function test_custom_role_update_with_permission_names()
    {
        $user = User::factory()->create();
        $user->addRoleById(config('anjan001.roles-and-permissions.super_admin_role_id', 1));
        $this->actingAs($user);

        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Role to Update 2',
            'type' => 'Custom',
            'is_enabled' => 1,
        ]);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'update.perm.name',
            'is_enabled' => 1,
        ]);
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $response = $this->putJson('/api/anjan011/roles-and-permissions/custom-roles/'.$role->$roleIdCol, [
            'permission_names' => ['update.perm.name'],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas(config('anjan001.roles-and-permissions.role_permission_table'), [
            $permissionIdCol => $permission->$permissionIdCol,
            'role_id' => $role->$roleIdCol,
        ]);
    }
}
