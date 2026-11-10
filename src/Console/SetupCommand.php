<?php

namespace Anjan011\RolesAndPermissions\Console;

use Anjan011\RolesAndPermissions\Models\Permission;
use Anjan011\RolesAndPermissions\Models\Role;
use Anjan011\RolesAndPermissions\RolesAndPermissionsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetupCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:setup';

    protected $description = 'Perform database migrations and initial setup for roles and permissions';

    public function handle(): int
    {
        $this->info('Starting Roles and Permissions setup...');

        // 1. Run Migrations
        $this->info('Running database migrations...');
        Artisan::call('migrate', [
            '--force' => true,
        ], $this->getOutput());
        $this->info('Migrations completed.');

        // 2. Seed Initial Data
        $this->seedInitialData();

        // 3. Cleanup old system-role permissions (if any)
        $this->cleanupOldPermissions();

        // 4. Assign Super Admin role
        $superAdminIds = config('anjan001.roles-and-permissions.super_admin_user_ids', []);

        if (! empty($superAdminIds)) {
            $this->info('Assigning Super Admin role to configured user IDs...');

            $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
            $superAdminRole = Role::where($roleIdCol, 1)->first();

            if (! $superAdminRole) {
                $this->error('Super Admin role (ID 1) not found. Please ensure migrations ran correctly.');

                return 1;
            }

            $userIdCol = config('anjan001.roles-and-permissions.user_id_column', 'id');
            $usersTable = config('anjan001.roles-and-permissions.users_table_name', 'users');

            foreach ($superAdminIds as $userId) {
                $user = DB::table($usersTable)->where($userIdCol, $userId)->first();
                if ($user) {
                    RolesAndPermissionsManager::addRoleById($userId, 1);
                    $this->line("Assigned Super Admin role to user ID: $userId");
                } else {
                    $this->warn("User with ID $userId not found.");
                }
            }
        } else {
            $this->info('No Super Admin user IDs configured in roles-and-permissions.php.');
        }

        $this->info('Setup completed successfully.');

        return 0;
    }

    private function cleanupOldPermissions(): void
    {
        $oldPermissions = [
            'system-role.view',
            'system-role.create',
            'system-role.update',
            'system-role.delete',
            'system-role.import',
            'system-role.export',
        ];

        $count = Permission::whereIn('name', $oldPermissions)->delete();
        if ($count > 0) {
            $this->info("Cleaned up $count legacy system-role permissions.");
        }
    }

    private function seedInitialData(): void
    {
        $this->info('Seeding initial roles and permissions...');

        // 1. Create Admin Role (Custom)
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            [
                'role_uid' => (string) Str::uuid(),
                'type' => 'Custom',
                'description' => 'Predefined Admin role with limited system access',
                'is_enabled' => 1,
            ]
        );

        // 2. Define Permissions
        $permissions = [
            // User Permissions
            'user.view' => 'Allow user to view users',
            'user.create' => 'Allow user to create users',
            'user.update' => 'Allow user to update users',
            'user.delete' => 'Allow user to delete users',
            'user.import' => 'Allow user to import users',
            'user.export' => 'Allow user to export users',

            // Custom Role Permissions
            'custom-role.view' => 'Allow user to view custom roles',
            'custom-role.create' => 'Allow user to create custom roles',
            'custom-role.update' => 'Allow user to update custom roles',
            'custom-role.delete' => 'Allow user to delete custom roles',
            'custom-role.import' => 'Allow user to import custom roles',
            'custom-role.export' => 'Allow user to export custom roles',
        ];

        $permissionModels = [];
        foreach ($permissions as $name => $description) {
            $permissionModels[$name] = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_uid' => (string) Str::uuid(),
                    'type' => 'System',
                    'description' => $description,
                    'is_enabled' => 1,
                ]
            );
        }

        // 3. Assign Permissions to Admin Role
        $adminPermissions = [
            'custom-role.view',
            'custom-role.create',
            'custom-role.update',
            'user.view',
            'user.create',
            'user.update',
            'user.export',
            'user.import',
            'user.delete',
        ];

        $idsToSync = [];
        foreach ($adminPermissions as $permName) {
            if (isset($permissionModels[$permName])) {
                $idsToSync[] = $permissionModels[$permName]->getKey();
            }
        }

        $adminRole->permissions()->syncWithoutDetaching($idsToSync);

        $this->info('Initial roles and permissions seeded.');
    }
}
