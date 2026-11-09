<?php

namespace Anjan011\RolesAndPermissions\Traits;

use Anjan011\RolesAndPermissions\Models\Permission;
use Illuminate\Http\Request;

trait HandlesPermissionAssignment
{
    protected function assignPermissionsFromRequest($role, Request $request)
    {
        $permissionIds = $request->get('permission_ids');
        $permissionNames = $request->get('permission_names');

        if (! empty($permissionIds) && is_array($permissionIds)) {
            // Validate integers
            foreach ($permissionIds as $id) {
                if (! is_int($id)) {
                    return response()->json(['success' => false, 'message' => 'All permission_ids must be integers.'], 400);
                }
            }

            // Validate existence
            try {
                Permission::validatePermissions($permissionIds);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            $role->permissions()->sync($permissionIds);

            return null;
        }

        if (! empty($permissionNames) && is_array($permissionNames)) {
            // Validate strings
            foreach ($permissionNames as $name) {
                if (! is_string($name)) {
                    return response()->json(['success' => false, 'message' => 'All permission_names must be strings.'], 400);
                }
            }

            // Validate existence
            try {
                Permission::validatePermissions($permissionNames);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            // Resolve IDs from names
            $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
            $ids = Permission::whereIn('name', $permissionNames)->pluck($permissionIdCol)->toArray();

            $role->permissions()->sync($ids);

            return null;
        }

        return null;
    }
}
