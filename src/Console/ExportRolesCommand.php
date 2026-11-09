<?php

namespace Anjan011\RolesAndPermissions\Console;

use Anjan011\RolesAndPermissions\Models\Role;
use Illuminate\Console\Command;

class ExportRolesCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:export-roles {--file= : The path to the CSV file}';

    protected $description = 'Export roles to a CSV file';

    public function handle(): int
    {
        $filePath = $this->option('file');

        if (! $filePath) {
            $this->error('File path is required.');

            return 1;
        }

        $roles = Role::all();

        $file = fopen($filePath, 'w');

        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');
        $header = [$roleIdCol, 'role_uid', 'name', 'type', 'description', 'is_enabled', 'created_at', 'updated_at'];
        fputcsv($file, $header);

        foreach ($roles as $role) {
            fputcsv($file, [
                $role->$roleIdCol,
                $role->role_uid,
                $role->name,
                $role->type,
                $role->description,
                $role->is_enabled,
                $role->created_at,
                $role->updated_at,
            ]);
        }

        fclose($file);

        $this->info("Roles exported to $filePath");

        return 0;
    }
}
