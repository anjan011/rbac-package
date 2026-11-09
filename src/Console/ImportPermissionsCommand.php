<?php

namespace Anjan011\RolesAndPermissions\Console;

use Anjan011\RolesAndPermissions\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportPermissionsCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:import-permissions {--file= : The path to the CSV file}';

    protected $description = 'Import permissions from a CSV file';

    public function handle(): int
    {
        $filePath = $this->option('file');

        if (! $filePath || ! file_exists($filePath)) {
            $this->error('File not found.');

            return 1;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        if (! $header) {
            $this->error('Empty CSV file.');
            fclose($file);

            return 1;
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        $permissionIdCol = config('anjan001.roles-and-permissions.permission_id_column', 'permission_id');

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            try {
                $permissionData = [
                    'name' => $data['name'] ?? null,
                    'description' => $data['description'] ?? null,
                    'is_enabled' => $data['is_enabled'] ?? 1,
                    'permission_uid' => $data['permission_uid'] ?? (string) Str::uuid(),
                ];

                if (isset($data[$permissionIdCol])) {
                    $permissionData[$permissionIdCol] = $data[$permissionIdCol];
                } elseif (isset($data['permission_id'])) {
                    $permissionData[$permissionIdCol] = $data['permission_id'];
                }

                Permission::create($permissionData);
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = 'Failed to import permission '.($data['name'] ?? 'unknown').': '.$e->getMessage();
            }
        }

        fclose($file);

        $this->info("Imported: $imported");
        $this->info("Failed: $failed");

        if ($failed > 0) {
            foreach ($errors as $error) {
                $this->warn($error);
            }
        }

        return 0;
    }
}
