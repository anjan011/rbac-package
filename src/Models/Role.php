<?php

namespace Anjan011\RolesAndPermissions\Models;

use Anjan011\RolesAndPermissions\RolesAndPermissionsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('anjan001.roles-and-permissions.roles_table', 'roles');
        $this->primaryKey = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($role) {
            if ($role->getOriginal('type') === 'System') {
                $user = auth()->user();
                $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);

                if (! $user || ! RolesAndPermissionsManager::hasRoleById($user->getAuthIdentifier(), $superAdminRoleId)) {
                    throw new \Exception('System roles cannot be modified.');
                }
            }
        });

        static::deleting(function ($role) {
            $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
            $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);

            if ($superAdminRoleId == $role->$roleIdCol) {
                throw new \Exception('Super Admin role cannot be deleted.');
            }

            if ($role->type === 'System') {
                $user = auth()->user();

                if (! $user || ! RolesAndPermissionsManager::hasRoleById($user->getAuthIdentifier(), $superAdminRoleId)) {
                    throw new \Exception('System roles cannot be deleted.');
                }
            }
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            config('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref'),
            config('anjan001.roles-and-permissions.role_id_column', 'role_id'),
            config('anjan001.roles-and-permissions.permission_id_column', 'permission_id')
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        $userIdCol = config('anjan001.roles-and-permissions.user_id_column', 'id');
        $pivotUserIdCol = ($userIdCol === 'id') ? 'user_id' : $userIdCol;

        return $this->belongsToMany(
            $userModel,
            config('anjan001.roles-and-permissions.user_role_table', 'user_role_xref'),
            config('anjan001.roles-and-permissions.role_id_column', 'role_id'),
            $pivotUserIdCol
        )->withTimestamps();
    }

    public static function validateRole(string|int $role): void
    {
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $query = static::query();

        if (is_int($role)) {
            $query->where($roleIdCol, $role);
        } else {
            $query->where('name', $role);
        }

        $roleModel = $query->first();

        if (! $roleModel || $roleModel->is_enabled != 1) {
            throw new \Exception('Invalid role');
        }
    }

    public static function validateRoles(array $roles): void
    {
        foreach ($roles as $role) {
            static::validateRole($role);
        }
    }
}
