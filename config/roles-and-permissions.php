<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Settings
    |--------------------------------------------------------------------------
    */
    'user_id_column' => 'id',
    'users_table_name' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Super Admin Settings
    |--------------------------------------------------------------------------
    */
    'super_admin_user_ids' => [],
    'super_admin_role_id' => 1,

    /*
    |--------------------------------------------------------------------------
    | Roles Settings
    |--------------------------------------------------------------------------
    */
    'roles_table' => 'roles',
    'role_id_column' => 'role_id',
    'user_role_table' => 'user_role_xref',

    /*
    |--------------------------------------------------------------------------
    | Permissions Settings
    |--------------------------------------------------------------------------
    */
    'permissions_table' => 'permissions',
    'permission_id_column' => 'permission_id',
    'role_permission_table' => 'role_permission_xref',

    /*
    |--------------------------------------------------------------------------
    | Permissions Dump Settings
    |--------------------------------------------------------------------------
    |
    | This settings are used by the anjan011-roles-permissions:dump-permissions
    | command to generate a PHP file with permission constants.
    |
    */
    'permissions_dump' => [
        'path' => base_path('app/Constants/permissions.php'),
        'prefix' => 'PERM',
    ],

    /*
    |--------------------------------------------------------------------------
    | Package API Routes Configuration
    |--------------------------------------------------------------------------
    |
    | This way the user can decide whether to enable or disable the endpoints,
    | including the prefix and middleware.
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'api/anjan011/roles-and-permissions',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Names
    |--------------------------------------------------------------------------
    |
    | These are the names of the middleware that will be registered by the
    | package. You can change them to avoid conflicts.
    |
    */
    'middleware_name' => [
        'permission' => 'anjan011-rbac-permission',
        'role' => 'anjan011-rbac-role',
    ],
];
