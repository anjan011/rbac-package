<?php

namespace Anjan011\RolesAndPermissions\Console;

use Illuminate\Console\Command;

class SetupInstructionsCommand extends Command
{
    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'anjan011-roles-permissions:setup-instructions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the necessary steps to setup the package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('====================================================================');
        $this->info('   anjan011/roles-and-permissions - Setup Instructions');
        $this->info('====================================================================');
        $this->newLine();

        $this->comment('1. Check and Edit the config file if needed:');
        $this->line('   Path: config/anjan001/roles-and-permissions.php');
        $this->newLine();

        $this->comment('2. Add the `UseRolesAndPermissions` trait to the User model:');
        $this->line('   File: app/Models/User.php');
        $this->line('   Trait: Anjan011\RolesAndPermissions\Traits\UseRolesAndPermissions');
        $this->newLine();

        $this->comment('3. Perform the setup artisan command:');
        $this->line('   Command: php artisan anjan011-roles-permissions:setup');
        $this->newLine();
        $this->info('   Note: This setup command will perform the migration and seed the');
        $this->info('   database with default roles and permissions.');
        $this->newLine();
        $this->info('====================================================================');
        $this->newLine();

        return 0;
    }
}
