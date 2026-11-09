<?php

use Anjan011\RolesAndPermissions\Http\Controllers\Api\CustomRoleController;
use Anjan011\RolesAndPermissions\Http\Controllers\Api\PermissionController;
use Anjan011\RolesAndPermissions\Http\Controllers\Api\SystemRoleController;
use Illuminate\Support\Facades\Route;

$prefix = config('anjan001.roles-and-permissions.routes.prefix', 'api/anjan011/roles-and-permissions');
$middleware = config('anjan001.roles-and-permissions.routes.middleware', ['api', 'auth:sanctum']);

$permissionMiddlewareName = config('anjan001.roles-and-permissions.middleware_name.permission', 'anjan011-rbac-permission');
$roleMiddlewareName = config('anjan001.roles-and-permissions.middleware_name.role', 'anjan011-rbac-role');

Route::group(['prefix' => $prefix, 'middleware' => $middleware], function () use ($permissionMiddlewareName, $roleMiddlewareName) {

    // System Roles and Permissions - Only Super Admin
    Route::group(['middleware' => "{$roleMiddlewareName}:Super Admin"], function () {
        Route::apiResource('system-roles', SystemRoleController::class);
        Route::apiResource('permissions', PermissionController::class);
    });

    // Custom Roles - Permission based
    Route::group(['middleware' => "{$permissionMiddlewareName}:custom-role.view"], function () {
        Route::get('custom-roles', [CustomRoleController::class, 'index']);
        Route::get('custom-roles/{id}', [CustomRoleController::class, 'show']);
    });

    Route::group(['middleware' => "{$permissionMiddlewareName}:custom-role.create"], function () {
        Route::post('custom-roles', [CustomRoleController::class, 'store']);
    });

    Route::group(['middleware' => "{$permissionMiddlewareName}:custom-role.update"], function () {
        Route::put('custom-roles/{id}', [CustomRoleController::class, 'update']);
        Route::patch('custom-roles/{id}', [CustomRoleController::class, 'update']);
    });

    Route::group(['middleware' => "{$permissionMiddlewareName}:custom-role.delete"], function () {
        Route::delete('custom-roles/{id}', [CustomRoleController::class, 'destroy']);
    });
});
