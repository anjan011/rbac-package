<?php

namespace Anjan011\RolesAndPermissions\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected $signature = 'anjan011-roles-permissions:test';

    protected $description = 'Run unit tests for the roles and permissions package';

    public function handle(): int
    {
        $this->info('Running Roles and Permissions unit tests...');

        $testPath = realpath(__DIR__.'/../../tests');
        $phpunitPath = base_path('vendor/bin/phpunit');

        // We use phpunit directly on the package tests directory with TestDox for readable output
        $process = new Process([$phpunitPath, $testPath, '--testdox', '--colors=always']);

        // Ensure we capture output in real-time
        $process->setTimeout(null);

        try {
            $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });
        } catch (\Exception $e) {
            $this->error('An error occurred while running tests: '.$e->getMessage());

            return 1;
        }

        if ($process->isSuccessful()) {
            $this->info('All tests passed!');

            return 0;
        } else {
            $this->error('Tests failed.');

            return 1;
        }
    }
}
