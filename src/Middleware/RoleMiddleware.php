<?php

namespace Anjan011\RolesAndPermissions\Middleware;

use Anjan011\RolesAndPermissions\Models\Role;
use Anjan011\RolesAndPermissions\RolesAndPermissionsManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized.');
        }

        try {
            // Validate all roles exist and are enabled first
            Role::validateRoles($roles);

            // Check for Super Admin override
            $superAdminRoleId = config('anjan001.roles-and-permissions.super_admin_role_id', 1);
            if (RolesAndPermissionsManager::hasRoleById($request->user()->getAuthIdentifier(), $superAdminRoleId)) {
                return $next($request);
            }

            if (! RolesAndPermissionsManager::hasAnyRoleByName($request->user()->getAuthIdentifier(), $roles)) {
                abort(403, 'You do not have the required roles.');
            }
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Invalid role') {
                abort(403, 'Invalid role');
            }
            throw $e;
        }

        return $next($request);
    }
}
