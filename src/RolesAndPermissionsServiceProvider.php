<?php

    namespace Anjan011\RolesAndPermissions;

    use Illuminate\Console\Events\CommandStarting;
    use Illuminate\Support\Facades\Event;
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

                // Listen for the package:discover command execution
                Event::listen(CommandStarting::class, function (CommandStarting $event) {
                    if ($event->command === 'package:discover') {
                        $this->app->booted(function () {
                            $this->call(Console\SetupInstructionsCommand::class);
                        });
                    }
                });
            }
        }
    }
