<?php

namespace Anjan011\RolesAndPermissions\Http\Controllers\Api;

use Anjan011\RolesAndPermissions\Models\Role;
use Anjan011\RolesAndPermissions\Traits\HandlesPermissionAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CustomRoleController extends Controller
{
    use HandlesPermissionAssignment;

    public function index()
    {
        $roles = Role::where('type', 'Custom')->get();

        return response()->json(['success' => true, 'data' => $roles]);
    }

    public function show($id)
    {
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('type', 'Custom')->where($roleIdCol, $id)->with('permissions')->firstOrFail();

        return response()->json(['success' => true, 'data' => $role]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:'.config('anjan001.roles-and-permissions.roles_table', 'roles').',name',
            'is_enabled' => 'boolean',
            'permission_ids' => 'array',
            'permission_names' => 'array',
        ]);

        $role = Role::create([
            'role_uid' => (string) Str::uuid(),
            'name' => $request->name,
            'type' => 'Custom',
            'is_enabled' => $request->get('is_enabled', 1),
        ]);

        $result = $this->assignPermissionsFromRequest($role, $request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json(['success' => true, 'message' => 'Custom role created successfully', 'data' => $role->load('permissions')], 201);
    }

    public function update(Request $request, $id)
    {
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('type', 'Custom')->where($roleIdCol, $id)->firstOrFail();

        $request->validate([
            'name' => 'string|unique:'.config('anjan001.roles-and-permissions.roles_table', 'roles').',name,'.$id.','.$roleIdCol,
            'is_enabled' => 'boolean',
            'permission_ids' => 'array',
            'permission_names' => 'array',
        ]);

        $role->update($request->only(['name', 'is_enabled']));

        $result = $this->assignPermissionsFromRequest($role, $request);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json(['success' => true, 'message' => 'Custom role updated successfully', 'data' => $role->load('permissions')]);
    }

    public function destroy($id)
    {
        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $role = Role::where('type', 'Custom')->where($roleIdCol, $id)->firstOrFail();

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Custom role deleted successfully']);
    }
}
