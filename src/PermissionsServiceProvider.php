<?php

namespace Javaabu\Permissions;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Javaabu\Permissions\Providers\EventServiceProvider;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'permissions-migrations');

            $this->publishes([
                __DIR__ . '/../config/permission.php' => config_path('permission.php'),
            ], 'permissions-config');
        }

        /**
         * Returns true if the user has any of the permissions
         */
        Blade::if('anypermission', function ($permissions) {
            if ($user = auth()->user()) {
                return $user->anyPermission($permissions);
            }

            return false;
        });
    }

    /**
     * Register migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Permissions::$runsMigrations) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');

        $this->app->register(EventServiceProvider::class);
    }
}
