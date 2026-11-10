<?php

    namespace Anjan011\RolesAndPermissions;

    use Illuminate\Support\ServiceProvider;

    class RolesAndPermissionsServiceProvider extends ServiceProvider
    {
        /**
         * Register services.
         */
        public function register(): void
        {
            $this->mergeConfigFrom(
                __DIR__.'/../config/roles-and-permissions.php', 'anjan001.roles-and-permissions'
            );

            $this->app->singleton('roles-and-permissions', function ($app) {
                return new RolesAndPermissionsManager($app);
            });
        }

        /**
         * Bootstrap services.
         */
        public function boot(): void
        {
            // Register middleware
            $permissionMiddleware = config('anjan001.roles-and-permissions.middleware_name.permission', 'anjan011-rbac-permission');
            $roleMiddleware = config('anjan001.roles-and-permissions.middleware_name.role', 'anjan011-rbac-role');

            $this->app['router']->aliasMiddleware($permissionMiddleware, Middleware\PermissionMiddleware::class);
            $this->app['router']->aliasMiddleware($roleMiddleware, Middleware\RoleMiddleware::class);

            // Load API routes
            if (config('anjan001.roles-and-permissions.routes.enabled')) {
                $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
            }

            if ($this->app->runningInConsole()) {
                $this->publishes([
                    __DIR__.'/../config/roles-and-permissions.php' => config_path('anjan001/roles-and-permissions.php'),
                ], 'config');

                $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

                // Register commands
                $this->commands([
                    Console\SetupCommand::class,
                    Console\SetupInstructionsCommand::class,
                    Console\TestCommand::class,
                    Console\ImportRolesCommand::class,
                    Console\ExportRolesCommand::class,
                    Console\ImportPermissionsCommand::class,
                    Console\ExportPermissionsCommand::class,
                    Console\AssignRoleCommand::class,
                    Console\DumpPermissionsCommand::class,
                ]);

                // Print setup instructions directly when package is discovered
                if (isset($_SERVER['argv']) && in_array('package:discover', $_SERVER['argv'])) {
                    $this->printSetupInstructions();
                }
            }
        }

        /**
         * Print formatted setup instructions to STDOUT.
         */
        protected function printSetupInstructions(): void
        {
            fwrite(STDOUT, "\n");
            fwrite(STDOUT, "\033[1;32m====================================================================\033[0m\n");
            fwrite(STDOUT, "\033[1;32m    anjan011/roles-and-permissions - Setup Instructions\033[0m\n");
            fwrite(STDOUT, "\033[1;32m====================================================================\033[0m\n\n");

            fwrite(STDOUT, "\033[1;33m1. Check and Edit the config file if needed:\033[0m\n");
            fwrite(STDOUT, "   Path: config/anjan001/roles-and-permissions.php\n\n");

            fwrite(STDOUT, "\033[1;33m2. Add the `UseRolesAndPermissions` trait to the User model:\033[0m\n");
            fwrite(STDOUT, "   File: app/Models/User.php\n");
            fwrite(STDOUT, "   Trait: Anjan011\\RolesAndPermissions\\Traits\\UseRolesAndPermissions\n\n");

            fwrite(STDOUT, "\033[1;33m3. Perform the setup artisan command:\033[0m\n");
            fwrite(STDOUT, "   Command: php artisan anjan011-roles-permissions:setup\n\n");
            fwrite(STDOUT, "   Note: This setup command will perform the migration and seed the\n");
            fwrite(STDOUT, "   database with default roles and permissions.\n\n");

            fwrite(STDOUT, "\033[1;32m====================================================================\033[0m\n\n");
        }
    }
