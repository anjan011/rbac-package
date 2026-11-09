<?php

namespace Anjan011\RolesAndPermissions\Console;

use Anjan011\RolesAndPermissions\Models\Permission;
use Illuminate\Console\Command;

class ExportPermissionsCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:export-permissions {--file= : The path to the CSV file}';

    protected $description = 'Export permissions to a CSV file';

    public function handle(): int
    {
        $filePath = $this->option('file');

        if (! $filePath) {
            $this->error('File path is required.');

            return 1;
        }

        $permissions = Permission::all();

        $file = fopen($filePath, 'w');

        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');
        $header = [$permissionIdCol, 'permission_uid', 'name', 'description', 'is_enabled', 'created_at', 'updated_at'];
        fputcsv($file, $header);

        foreach ($permissions as $permission) {
            fputcsv($file, [
                $permission->$permissionIdCol,
                $permission->permission_uid,
                $permission->name,
                $permission->description,
                $permission->is_enabled,
                $permission->created_at,
                $permission->updated_at,
            ]);
        }

        fclose($file);

        $this->info("Permissions exported to $filePath");

        return 0;
    }
}
