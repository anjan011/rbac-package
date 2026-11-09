<?php

namespace Anjan011\RolesAndPermissions\Console;

use Anjan011\RolesAndPermissions\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportRolesCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:import-roles {--file= : The path to the CSV file}';

    protected $description = 'Import roles from a CSV file';

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

        $roleIdCol = config('anjan001.roles-and-permissions.role_id_column', 'role_id');

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            try {
                $roleData = [
                    'name' => $data['name'] ?? null,
                    'type' => $data['type'] ?? 'Custom',
                    'description' => $data['description'] ?? null,
                    'is_enabled' => $data['is_enabled'] ?? 1,
                    'role_uid' => $data['role_uid'] ?? (string) Str::uuid(),
                ];

                if (isset($data[$roleIdCol])) {
                    $roleData[$roleIdCol] = $data[$roleIdCol];
                } elseif (isset($data['role_id'])) {
                    $roleData[$roleIdCol] = $data['role_id'];
                }

                Role::create($roleData);
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = 'Failed to import role '.($data['name'] ?? 'unknown').': '.$e->getMessage();
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
