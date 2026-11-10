<?php

namespace Anjan011\RolesAndPermissions\Middleware;

use Anjan011\RolesAndPermissions\RolesAndPermissionsManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized.');
        }

        try {
            if (! RolesAndPermissionsManager::hasAllPermissionsByName($request->user()->getAuthIdentifier(), $permissions)) {
                abort(403, 'You do not have the required permissions.');
            }
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Invalid permission') {
                abort(403, 'Invalid permission');
            }
            throw $e;
        }

        return $next($request);
    }
}
