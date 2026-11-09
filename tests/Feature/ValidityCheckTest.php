<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ValidityCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_has_role_by_name_throws_exception_for_invalid_role()
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid role');

        $user->hasRoleByName('NonExistentRole');
    }

    public function test_has_role_by_name_throws_exception_for_disabled_role()
    {
        $user = User::factory()->create();

        Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => 'DisabledRole',
            'type' => 'Custom',
            'is_enabled' => 0,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid role');

        $user->hasRoleByName('DisabledRole');
    }

    public function test_has_permission_by_name_throws_exception_for_invalid_permission()
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid permission');

        $user->hasPermissionByName('non.existent.permission');
    }

    public function test_has_permission_by_name_throws_exception_for_disabled_permission()
    {
        $user = User::factory()->create();

        Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => 'disabled.permission',
            'type' => 'Custom',
            'is_enabled' => 0,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid permission');

        $user->hasPermissionByName('disabled.permission');
    }

    public function test_add_role_by_id_throws_exception_for_invalid_role()
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid role');

        $user->addRoleById(9999);
    }
}
