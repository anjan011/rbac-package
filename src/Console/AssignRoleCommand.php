<?php

namespace Anjan011\RolesAndPermissions\Console;

use Anjan011\RolesAndPermissions\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class AssignRoleCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:assign-role 
                            {--role-id= : The ID of the role to assign} 
                            {--user-id= : The ID of the user to assign the role to} 
                            {--force : Force assign the role even if the user already has it} 
                            {--role-name= : The name of the role to assign} 
                            {--user-email= : The email of the user to assign the role to}';

    protected $description = 'Assign a role to a user';

    public function handle(): int
    {
        $roleId = $this->option('role-id');
        $userId = $this->option('user-id');
        $roleName = $this->option('role-name');
        $userEmail = $this->option('user-email');
        $force = $this->option('force');

        $userIdCol = Config::get('anjan001.roles-and-permissions.user_id_column', 'id');
        $roleIdCol = Config::get('anjan001.roles-and-permissions.role_id_column', 'role_id');

        // Validate User
        if (! $userId && ! $userEmail) {
            $this->error('Either --user-id or --user-email is required.');

            return 1;
        }

        $userModel = Config::get('auth.providers.users.model', 'App\\Models\\User');
        $user = null;

        if ($userId) {
            $user = (new $userModel)->where($userIdCol, $userId)->first();
            if (! $user) {
                $this->error("User with ID {$userId} not found.");

                return 1;
            }
        } else {
            $user = (new $userModel)->where('email', $userEmail)->first();
            if (! $user) {
                $this->error("User with email {$userEmail} not found.");

                return 1;
            }
        }

        // Validate Role
        if (! $roleId && ! $roleName) {
            $this->error('Either --role-id or --role-name is required.');

            return 1;
        }

        $role = null;

        if ($roleId) {
            $role = Role::where($roleIdCol, $roleId)->first();
            if (! $role) {
                $this->error("Role with ID {$roleId} not found.");

                return 1;
            }
        } else {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                $this->error("Role with name '{$roleName}' not found.");

                return 1;
            }
        }

        $targetRoleId = $role->$roleIdCol;
        $targetRoleName = $role->name;

        // Check if user already has the role
        if (! $force && $user->hasRoleById($targetRoleId)) {
            $this->info("User '{$user->name}' ({$user->email}, ID: {$user->$userIdCol}) already has the role '{$targetRoleName}'.");

            return 0;
        }

        // Assign Role
        try {
            $user->addRoleById($targetRoleId);
            $this->info("Successfully assigned role '{$targetRoleName}' to user '{$user->name}' ({$user->email}, ID: {$user->$userIdCol}).");

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to assign role: {$e->getMessage()}");

            return 1;
        }
    }
}
