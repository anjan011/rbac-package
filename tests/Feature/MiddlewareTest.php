<?php

namespace Anjan011\RolesAndPermissions\Tests\Feature;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions used in middleware tests
        Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'edit-post', 'is_enabled' => 1]);
        Permission::create(['permission_uid' => (string) Str::uuid(), 'name' => 'delete-post', 'is_enabled' => 1]);
        Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Editor', 'is_enabled' => 1]);
        Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Admin', 'is_enabled' => 1]);

        $permissionMiddleware = config('anjan001.roles-and-permissions.middleware_name.permission', 'anjan011-rbac-permission');
        $roleMiddleware = config('anjan001.roles-and-permissions.middleware_name.role', 'anjan011-rbac-role');

        Route::get('/test-permission-middleware', function () {
            return 'access granted';
        })->middleware($permissionMiddleware.':edit-post,delete-post');

        Route::get('/test-role-middleware', function () {
            return 'access granted';
        })->middleware($roleMiddleware.':Editor,Admin');
    }

    public function test_permission_middleware_denies_access_if_user_lacks_permissions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/test-permission-middleware');
        $response->assertStatus(403);
    }

    public function test_permission_middleware_allows_access_if_user_has_all_permissions()
    {
        $user = User::factory()->create();

        // Assign role that has the permissions
        $role = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Post Editor', 'is_enabled' => 1]);
        $role->permissions()->attach(Permission::whereIn('name', ['edit-post', 'delete-post'])->pluck(config('anjan001.roles-and-permissions.permission_id_column'))->toArray());

        $user->addRoleByName('Post Editor');

        $this->actingAs($user);

        $response = $this->get('/test-permission-middleware');
        $response->assertStatus(200);
        $response->assertSee('access granted');
    }

    public function test_permission_middleware_denies_access_if_user_has_only_some_permissions()
    {
        $user = User::factory()->create();

        // Assign role that has only one permission
        $role = Role::create(['role_uid' => (string) Str::uuid(), 'name' => 'Partial Editor', 'is_enabled' => 1]);
        $role->permissions()->attach(Permission::where('name', 'edit-post')->first()->{config('anjan001.roles-and-permissions.permission_id_column')});

        $user->addRoleByName('Partial Editor');

        $this->actingAs($user);

        $response = $this->get('/test-permission-middleware');
        $response->assertStatus(403);
    }

    public function test_role_middleware_denies_access_if_user_lacks_roles()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/test-role-middleware');
        $response->assertStatus(403);
    }

    public function test_role_middleware_allows_access_if_user_has_any_role()
    {
        $user = User::factory()->create();
        $user->addRoleByName('Editor');

        $this->actingAs($user);

        $response = $this->get('/test-role-middleware');
        $response->assertStatus(200);
        $response->assertSee('access granted');

        $user2 = User::factory()->create();
        $user2->addRoleByName('Admin');

        $this->actingAs($user2);
        $response = $this->get('/test-role-middleware');
        $response->assertStatus(200);
    }
}
