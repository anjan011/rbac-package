<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class MiddlewareChangesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permissionMiddleware = config('anjan001.roles-and-permissions.middleware_name.permission', 'anjan011-rbac-permission');
        $roleMiddleware = config('anjan001.roles-and-permissions.middleware_name.role', 'anjan011-rbac-role');

        Route::get('/test-perm-mid', function () {
            return 'access granted';
        })->middleware($permissionMiddleware.':existing-perm');

        Route::get('/test-perm-invalid-mid', function () {
            return 'access granted';
        })->middleware($permissionMiddleware.':non-existent-perm');

        Route::get('/test-role-mid', function () {
            return 'access granted';
        })->middleware($roleMiddleware.':existing-role');

        Route::get('/test-role-invalid-mid', function () {
            return 'access granted';
        })->middleware($roleMiddleware.':non-existent-role');

        // Setup initial data
        Permission::updateOrCreate(['name' => 'existing-perm'], [
            'permission_uid' => (string) Str::uuid(),
            'is_enabled' => 1,
        ]);

        Role::updateOrCreate(['name' => 'existing-role'], [
            'role_uid' => (string) Str::uuid(),
            'is_enabled' => 1,
        ]);

        // Ensure Super Admin role exists (it should be seeded by migration already)
        if (! Role::where(config('anjan001.roles-and-permissions.role_id_column', 'role_id'), 1)->exists()) {
            Role::create([
                config('anjan001.roles-and-permissions.role_id_column', 'role_id') => 1,
                'role_uid' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'type' => 'System',
                'is_enabled' => 1,
            ]);
        }
    }

    public function test_permission_middleware_returns_403_for_invalid_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/test-perm-invalid-mid');

        $response->assertStatus(403);
        $this->assertEquals('Invalid permission', $response->json('message'));
    }

    public function test_permission_middleware_allows_super_admin_even_if_lacks_permission()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->addRoleById(1);
        $this->actingAs($superAdmin);

        $response = $this->getJson('/test-perm-mid');

        $response->assertStatus(200);
        $response->assertSee('access granted');
    }

    public function test_role_middleware_returns_403_for_invalid_role()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/test-role-invalid-mid');

        $response->assertStatus(403);
        $this->assertEquals('Invalid role', $response->json('message'));
    }

    public function test_role_middleware_allows_super_admin_even_if_lacks_role()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->addRoleById(1);
        $this->actingAs($superAdmin);

        $response = $this->getJson('/test-role-mid');

        $response->assertStatus(200);
        $response->assertSee('access granted');
    }
}
