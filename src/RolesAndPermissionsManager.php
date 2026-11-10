<?php

namespace Anjan011\RolesAndPermissions;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsManager
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public static function hasRoleByName(int $userId, string $name): bool
    {
        Role::validateRole($name);
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('name', $name)->first();

        return self::hasRoleById($userId, $role->$roleIdCol);
    }

    public static function hasRoleById(int $userId, int $role_id): bool
    {
        Role::validateRole($role_id);

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');

        return DB::table($userRoleTable)
            ->where($pivotUserIdCol, $userId)
            ->where($roleIdCol, $role_id)
            ->exists();
    }

    public static function hasAnyRoleByName(int $userId, array $role_names): bool
    {
        Role::validateRoles($role_names);
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $roleIds = Role::whereIn('name', $role_names)->pluck($roleIdCol)->toArray();

        return self::hasAnyRoleById($userId, $roleIds);
    }

    public static function hasAnyRoleById(int $userId, array $role_ids): bool
    {
        Role::validateRoles($role_ids);

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');

        return DB::table($userRoleTable)
            ->where($pivotUserIdCol, $userId)
            ->whereIn($roleIdCol, $role_ids)
            ->exists();
    }

    public static function hasPermissionByName(int $userId, string $name): bool
    {
        Permission::validatePermission($name);
        $permissionIdCol = Config::get('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $permission = Permission::where('name', $name)->first();

        return self::hasPermissionById($userId, $permission->$permissionIdCol);
    }

    public static function hasPermissionById(int $userId, int $permission_id): bool
    {
        Permission::validatePermission($permission_id);

        $superAdminRoleId = Config::get('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if (self::hasRoleById($userId, $superAdminRoleId)) {
            return true;
        }

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $rolePermissionTable = Config::get('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $permissionIdCol = Config::get('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        return DB::table($userRoleTable)
            ->join($rolePermissionTable, "{$userRoleTable}.{$roleIdCol}", '=', "{$rolePermissionTable}.{$roleIdCol}")
            ->where("{$userRoleTable}.{$pivotUserIdCol}", $userId)
            ->where("{$rolePermissionTable}.{$permissionIdCol}", $permission_id)
            ->exists();
    }

    public static function hasAnyPermissionByName(int $userId, array $permission_names): bool
    {
        Permission::validatePermissions($permission_names);
        $permissionIdCol = Config::get('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $permissionIds = Permission::whereIn('name', $permission_names)->pluck($permissionIdCol)->toArray();

        return self::hasAnyPermissionById($userId, $permissionIds);
    }

    public static function hasAnyPermissionById(int $userId, array $permission_ids): bool
    {
        Permission::validatePermissions($permission_ids);

        $superAdminRoleId = Config::get('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if (self::hasRoleById($userId, $superAdminRoleId)) {
            return true;
        }

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $rolePermissionTable = Config::get('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $permissionIdCol = Config::get('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        return DB::table($userRoleTable)
            ->join($rolePermissionTable, "{$userRoleTable}.{$roleIdCol}", '=', "{$rolePermissionTable}.{$roleIdCol}")
            ->where("{$userRoleTable}.{$pivotUserIdCol}", $userId)
            ->whereIn("{$rolePermissionTable}.{$permissionIdCol}", $permission_ids)
            ->exists();
    }

    public static function hasAllRolesByName(int $userId, array $role_names): bool
    {
        Role::validateRoles($role_names);
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $roleIds = Role::whereIn('name', $role_names)->pluck($roleIdCol)->toArray();

        return self::hasAllRolesById($userId, $roleIds);
    }

    public static function hasAllRolesById(int $userId, array $role_ids): bool
    {
        Role::validateRoles($role_ids);

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');

        $count = DB::table($userRoleTable)
            ->where($pivotUserIdCol, $userId)
            ->whereIn($roleIdCol, $role_ids)
            ->count();

        return $count === count(array_unique($role_ids));
    }

    public static function hasAllPermissionsByName(int $userId, array $permission_names): bool
    {
        Permission::validatePermissions($permission_names);
        $permissionIdCol = Config::get('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $permissionIds = Permission::whereIn('name', $permission_names)->pluck($permissionIdCol)->toArray();

        return self::hasAllPermissionsById($userId, $permissionIds);
    }

    public static function hasAllPermissionsById(int $userId, array $permission_ids): bool
    {
        Permission::validatePermissions($permission_ids);

        $superAdminRoleId = Config::get('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if (self::hasRoleById($userId, $superAdminRoleId)) {
            return true;
        }

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $rolePermissionTable = Config::get('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $permissionIdCol = Config::get('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        $count = DB::table($userRoleTable)
            ->join($rolePermissionTable, "{$userRoleTable}.{$roleIdCol}", '=', "{$rolePermissionTable}.{$roleIdCol}")
            ->where("{$userRoleTable}.{$pivotUserIdCol}", $userId)
            ->whereIn("{$rolePermissionTable}.{$permissionIdCol}", $permission_ids)
            ->distinct("{$rolePermissionTable}.{$permissionIdCol}")
            ->count("{$rolePermissionTable}.{$permissionIdCol}");

        return $count === count(array_unique($permission_ids));
    }

    public static function addRoleById(int $userId, int $role_id): void
    {
        Role::validateRole($role_id);

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');

        // Manual syncWithoutDetaching
        $exists = DB::table($userRoleTable)
            ->where($pivotUserIdCol, $userId)
            ->where($roleIdCol, $role_id)
            ->exists();

        if (!$exists) {
            DB::table($userRoleTable)->insert([
                $pivotUserIdCol => $userId,
                $roleIdCol => $role_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public static function addRoleByName(int $userId, string $role_name): void
    {
        Role::validateRole($role_name);
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('name', $role_name)->first();
        self::addRoleById($userId, $role->$roleIdCol);
    }

    public static function removeRoleById(int $userId, int $role_id): void
    {
        Role::validateRole($role_id);

        $userRoleTable = Config::get('anjan001.roles-and-permissions.user_role_table', 'user_role_xref');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');

        DB::table($userRoleTable)
            ->where($pivotUserIdCol, $userId)
            ->where($roleIdCol, $role_id)
            ->delete();
    }

    public static function removeRoleByName(int $userId, string $role_name): void
    {
        Role::validateRole($role_name);
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('name', $role_name)->first();
        self::removeRoleById($userId, $role->$roleIdCol);
    }

    public static function addRolesByIdList(int $userId, array $role_ids): void
    {
        Role::validateRoles($role_ids);
        foreach ($role_ids as $role_id) {
            self::addRoleById($userId, $role_id);
        }
    }

    public static function addRolesByNameList(int $userId, array $role_names): void
    {
        Role::validateRoles($role_names);
        foreach ($role_names as $role_name) {
            self::addRoleByName($userId, $role_name);
        }
    }

    public static function removeRolesByIdList(int $userId, array $role_ids): void
    {
        Role::validateRoles($role_ids);
        foreach ($role_ids as $role_id) {
            self::removeRoleById($userId, $role_id);
        }
    }

    public static function removeRolesByNameList(int $userId, array $role_names): void
    {
        Role::validateRoles($role_names);
        foreach ($role_names as $role_name) {
            self::removeRoleByName($userId, $role_name);
        }
    }
}
