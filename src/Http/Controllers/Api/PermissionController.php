<?php

namespace Anjan011\RolesAndPermissions\Http\Controllers\Api;

use Anjan011\RolesAndPermissions\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        return response()->json(['success' => true, 'data' => $permissions]);
    }

    public function show($id)
    {
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $permission = Permission::where($permissionIdCol, $id)->firstOrFail();

        return response()->json(['success' => true, 'data' => $permission]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:'.config('anjan001.roles-and-permissions.permissions_table', 'permissions').',name',
            'type' => 'required|in:System,Custom',
            'is_enabled' => 'boolean',
        ]);

        $permission = Permission::create([
            'permission_uid' => (string) Str::uuid(),
            'name' => $request->name,
            'type' => $request->type,
            'is_enabled' => $request->get('is_enabled', 1),
        ]);

        return response()->json(['success' => true, 'message' => 'Permission created successfully', 'data' => $permission], 201);
    }

    public function update(Request $request, $id)
    {
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $permission = Permission::where($permissionIdCol, $id)->firstOrFail();

        $request->validate([
            'name' => 'string|unique:'.config('anjan001.roles-and-permissions.permissions_table', 'permissions').',name,'.$id.','.$permissionIdCol,
            'type' => 'in:System,Custom',
            'is_enabled' => 'boolean',
        ]);

        try {
            $permission->update($request->only(['name', 'type', 'is_enabled']));

            return response()->json(['success' => true, 'message' => 'Permission updated successfully', 'data' => $permission]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $permission = Permission::where($permissionIdCol, $id)->firstOrFail();

        try {
            $permission->delete();

            return response()->json(['success' => true, 'message' => 'Permission deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
