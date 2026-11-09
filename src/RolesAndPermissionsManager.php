<?php

namespace Anjan011\RolesAndPermissions;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use Illuminate\Support\Facades\Config;

class RolesAndPermissionsManager
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    protected static function getUser(int $userId)
    {
        $userModel = Config::get('auth.providers.users.model', 'App\\Models\\User');
        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');

        return (new $userModel)->where($userIdCol, $userId)->first();
    }

    public static function hasRoleByName(int $userId, string $name): bool
    {
        Role::validateRole($name);
        $user = self::getUser($userId);

        return $user ? $user->hasRoleByName($name) : false;
    }

    public static function hasRoleById(int $userId, int $role_id): bool
    {
        Role::validateRole($role_id);
        $user = self::getUser($userId);

        return $user ? $user->hasRoleById($role_id) : false;
    }

    public static function hasAnyRoleByName(int $userId, array $role_names): bool
    {
        Role::validateRoles($role_names);
        $user = self::getUser($userId);

        return $user ? $user->hasAnyRoleByName($role_names) : false;
    }

    public static function hasAnyRoleById(int $userId, array $role_ids): bool
    {
        Role::validateRoles($role_ids);
        $user = self::getUser($userId);

        return $user ? $user->hasAnyRoleById($role_ids) : false;
    }

    public static function hasPermissionByName(int $userId, string $name): bool
    {
        Permission::validatePermission($name);
        $user = self::getUser($userId);

        return $user ? $user->hasPermissionByName($name) : false;
    }

    public static function hasPermissionById(int $userId, int $permission_id): bool
    {
        Permission::validatePermission($permission_id);
        $user = self::getUser($userId);

        return $user ? $user->hasPermissionById($permission_id) : false;
    }

    public static function hasAnyPermissionByName(int $userId, array $permission_names): bool
    {
        Permission::validatePermissions($permission_names);
        $user = self::getUser($userId);

        return $user ? $user->hasAnyPermissionByName($permission_names) : false;
    }

    public static function hasAnyPermissionById(int $userId, array $permission_ids): bool
    {
        Permission::validatePermissions($permission_ids);
        $user = self::getUser($userId);

        return $user ? $user->hasAnyPermissionById($permission_ids) : false;
    }

    public static function hasAllRolesByName(int $userId, array $role_names): bool
    {
        Role::validateRoles($role_names);
        $user = self::getUser($userId);

        return $user ? $user->hasAllRolesByName($role_names) : false;
    }

    public static function hasAllRolesById(int $userId, array $role_ids): bool
    {
        Role::validateRoles($role_ids);
        $user = self::getUser($userId);

        return $user ? $user->hasAllRolesById($role_ids) : false;
    }

    public static function hasAllPermissionsByName(int $userId, array $permission_names): bool
    {
        Permission::validatePermissions($permission_names);
        $user = self::getUser($userId);

        return $user ? $user->hasAllPermissionsByName($permission_names) : false;
    }

    public static function hasAllPermissionsById(int $userId, array $permission_ids): bool
    {
        Permission::validatePermissions($permission_ids);
        $user = self::getUser($userId);

        return $user ? $user->hasAllPermissionsById($permission_ids) : false;
    }

    public static function addRoleById(int $userId, int $role_id): void
    {
        Role::validateRole($role_id);
        $user = self::getUser($userId);
        if ($user) {
            $user->addRoleById($role_id);
        }
    }

    public static function addRoleByName(int $userId, string $role_name): void
    {
        Role::validateRole($role_name);
        $user = self::getUser($userId);
        if ($user) {
            $user->addRoleByName($role_name);
        }
    }

    public static function removeRoleById(int $userId, int $role_id): void
    {
        Role::validateRole($role_id);
        $user = self::getUser($userId);
        if ($user) {
            $user->removeRoleById($role_id);
        }
    }

    public static function removeRoleByName(int $userId, string $role_name): void
    {
        Role::validateRole($role_name);
        $user = self::getUser($userId);
        if ($user) {
            $user->removeRoleByName($role_name);
        }
    }

    public static function addRolesByIdList(int $userId, array $role_ids): void
    {
        Role::validateRoles($role_ids);
        $user = self::getUser($userId);
        if ($user) {
            $user->addRolesByIdList($role_ids);
        }
    }

    public static function addRolesByNameList(int $userId, array $role_names): void
    {
        Role::validateRoles($role_names);
        $user = self::getUser($userId);
        if ($user) {
            $user->addRolesByNameList($role_names);
        }
    }

    public static function removeRolesByIdList(int $userId, array $role_ids): void
    {
        Role::validateRoles($role_ids);
        $user = self::getUser($userId);
        if ($user) {
            $user->removeRolesByIdList($role_ids);
        }
    }

    public static function removeRolesByNameList(int $userId, array $role_names): void
    {
        Role::validateRoles($role_names);
        $user = self::getUser($userId);
        if ($user) {
            $user->removeRolesByNameList($role_names);
        }
    }
}
