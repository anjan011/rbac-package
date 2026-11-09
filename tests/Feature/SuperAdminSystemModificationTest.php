<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminSystemModificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('anjan011-roles-permissions:setup');
    }

    public function test_non_super_admin_cannot_modify_system_role()
    {
        $systemRole = Role::where('type', 'System')->first();
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('System roles cannot be modified.');

        $systemRole->update(['name' => 'Updated System Role']);
    }

    public function test_super_admin_can_modify_system_role()
    {
        $systemRole = Role::where('type', 'System')->first();
        $superAdmin = User::factory()->create();

        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        $superAdmin->addRoleById($superAdminRoleId);

        $this->actingAs($superAdmin);

        $systemRole->update(['name' => 'Updated System Role']);

        $this->assertEquals('Updated System Role', $systemRole->fresh()->name);
    }

    public function test_super_admin_role_cannot_be_deleted_even_by_super_admin()
    {
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        $superAdminRole = Role::where($roleIdCol, $superAdminRoleId)->first();

        $superAdminUser = User::factory()->create();
        $superAdminUser->addRoleById($superAdminRoleId);

        $this->actingAs($superAdminUser);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Super Admin role cannot be deleted.');

        $superAdminRole->delete();
    }

    public function test_super_admin_can_delete_system_role()
    {
        // Create another system role to test deletion, as Super Admin (ID 1) is protected
        $otherSystemRole = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'Deletable System Role',
            'type' => 'System',
            'is_enabled' => 1,
        ]);

        $superAdmin = User::factory()->create();
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        $superAdmin->addRoleById($superAdminRoleId);

        $this->actingAs($superAdmin);

        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $roleId = $otherSystemRole->$roleIdCol;

        $otherSystemRole->delete();

        $this->assertDatabaseMissing(config('anjan001.roles-and-permissions.roles_table'), [
            $roleIdCol => $roleId,
        ]);
    }
}
