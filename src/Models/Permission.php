<?php

namespace Anjan011\RolesAndPermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('anjan001.roles-and-permissions.permissions_table', 'permissions');
        $this->primaryKey = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($permission) {
            if ($permission->getOriginal('type') === 'System') {
                throw new \Exception('System permissions cannot be modified.');
            }
        });

        static::deleting(function ($permission) {
            if ($permission->type === 'System') {
                throw new \Exception('System permissions cannot be deleted.');
            }
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            config('anjan001.roles-and-permissions.role_permission_table', 'role_permission_xref'),
            config('anjan001.roles-and-permissions.permission_id_column', 'permission_id'),
            config('anjan001.roles-and-permissions.role_id_column', 'role_id')
        )->withTimestamps();
    }

    public static function validatePermission(string|int $permission): void
    {
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $query = static::query();

        if (is_int($permission)) {
            $query->where($permissionIdCol, $permission);
        } else {
            $query->where('name', $permission);
        }

        $permissionModel = $query->first();

        if (! $permissionModel || $permissionModel->is_enabled != 1) {
            throw new \Exception('Invalid permission');
        }
    }

    public static function validatePermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            static::validatePermission($permission);
        }
    }
}
