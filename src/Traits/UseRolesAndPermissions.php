<?php

namespace Anjan011\RolesAndPermissions\Traits;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait UseRolesAndPermissions
{
    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        $userIdCol = config('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;

        return $this->belongsToMany(
            Role::class,
            config('anjan001.roles-and-permissions.user_role_table', 'user_role_xref'),
            $pivotUserIdCol,
            config('anjan001.roles-and-permissions.role_id_column', 'role_id')
        )->withTimestamps();
    }

    /**
     * Get all permissions of the user through roles.
     */
    public function permissions(): Collection
    {
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->unique($permissionIdCol);
    }

    public function hasRoleByName(string $name): bool
    {
        Role::validateRole($name);
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');

        return $this->roles()->where($rolesTable.'.name', $name)->exists();
    }

    public function hasRoleById(int $role_id): bool
    {
        Role::validateRole($role_id);
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        return $this->roles()->where($rolesTable.'.'.$roleIdCol, $role_id)->exists();
    }

    public function hasAnyRoleByName(array $role_names): bool
    {
        Role::validateRoles($role_names);
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');

        return $this->roles()->whereIn($rolesTable.'.name', $role_names)->exists();
    }

    public function hasAnyRoleById(array $role_ids): bool
    {
        Role::validateRoles($role_ids);
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        return $this->roles()->whereIn($rolesTable.'.'.$roleIdCol, $role_ids)->exists();
    }

    public function hasPermissionByName(string $name): bool
    {
        Permission::validatePermission($name);
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if ($this->hasRoleById($superAdminRoleId)) {
            return true;
        }

        return $this->permissions()->contains('name', $name);
    }

    public function hasPermissionById(int $permission_id): bool
    {
        Permission::validatePermission($permission_id);
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if ($this->hasRoleById($superAdminRoleId)) {
            return true;
        }

        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        return $this->permissions()->contains($permissionIdCol, $permission_id);
    }

    public function hasAnyPermissionByName(array $permission_names): bool
    {
        Permission::validatePermissions($permission_names);
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if ($this->hasRoleById($superAdminRoleId)) {
            return true;
        }

        return $this->permissions()->whereIn('name', $permission_names)->isNotEmpty();
    }

    public function hasAnyPermissionById(array $permission_ids): bool
    {
        Permission::validatePermissions($permission_ids);
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if ($this->hasRoleById($superAdminRoleId)) {
            return true;
        }

        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        return $this->permissions()->whereIn($permissionIdCol, $permission_ids)->isNotEmpty();
    }

    public function hasAllRolesByName(array $role_names): bool
    {
        Role::validateRoles($role_names);
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $count = $this->roles()->whereIn($rolesTable.'.name', $role_names)->count();

        return $count === count(array_unique($role_names));
    }

    public function hasAllRolesById(array $role_ids): bool
    {
        Role::validateRoles($role_ids);
        $rolesTable = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $count = $this->roles()->whereIn($rolesTable.'.'.$roleIdCol, $role_ids)->count();

        return $count === count(array_unique($role_ids));
    }

    public function hasAllPermissionsByName(array $permission_names): bool
    {
        Permission::validatePermissions($permission_names);
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if ($this->hasRoleById($superAdminRoleId)) {
            return true;
        }

        $userPermissions = $this->permissions()->pluck('name')->toArray();
        foreach ($permission_names as $name) {
            if (! in_array($name, $userPermissions)) {
                return false;
            }
        }

        return true;
    }

    public function hasAllPermissionsById(array $permission_ids): bool
    {
        Permission::validatePermissions($permission_ids);
        $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
        if ($this->hasRoleById($superAdminRoleId)) {
            return true;
        }

        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $userPermissions = $this->permissions()->pluck($permissionIdCol)->toArray();
        foreach ($permission_ids as $id) {
            if (! in_array($id, $userPermissions)) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Methods for Managing Roles and Permissions
    |--------------------------------------------------------------------------
    */

    public function addRoleById(int $role_id): void
    {
        Role::validateRole($role_id);
        $this->roles()->syncWithoutDetaching([$role_id]);
    }

    public function addRoleByName(string $role_name): void
    {
        Role::validateRole($role_name);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('name', $role_name)->first();
        $this->addRoleById($role->$roleIdCol);
    }

    public function removeRoleById(int $role_id): void
    {
        Role::validateRole($role_id);
        $this->roles()->detach($role_id);
    }

    public function removeRoleByName(string $role_name): void
    {
        Role::validateRole($role_name);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('name', $role_name)->first();
        $this->removeRoleById($role->$roleIdCol);
    }

    public function addRolesByIdList(array $role_ids): void
    {
        Role::validateRoles($role_ids);
        $this->roles()->syncWithoutDetaching($role_ids);
    }

    public function addRolesByNameList(array $role_names): void
    {
        Role::validateRoles($role_names);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $roleIds = Role::whereIn('name', $role_names)->pluck($roleIdCol)->toArray();
        $this->addRolesByIdList($roleIds);
    }

    public function removeRolesByIdList(array $role_ids): void
    {
        Role::validateRoles($role_ids);
        $this->roles()->detach($role_ids);
    }

    public function removeRolesByNameList(array $role_names): void
    {
        Role::validateRoles($role_names);
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $roleIds = Role::whereIn('name', $role_names)->pluck($roleIdCol)->toArray();
        $this->removeRolesByIdList($roleIds);
    }
}
