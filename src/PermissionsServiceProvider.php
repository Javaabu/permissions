<?php

namespace Javaabu\Permissions;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Javaabu\Permissions\Commands\SyncPermissionsCommand;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Listeners\LogRolePermissionsUpdate;
use Javaabu\Permissions\Listeners\LogUserRoleUpdate;
use Javaabu\Permissions\Registry\PermissionRegistry;

class PermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');
        $this->mergeConfigFrom(__DIR__ . '/../config/permissions.php', 'permissions');

        $this->app->singleton(PermissionRegistry::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->registerPublishing();

            $this->commands([
                SyncPermissionsCommand::class,
            ]);
        }

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        $this->registerBladeDirectives();
        $this->registerActivityLogging();

        $this->app->booted(function () {
            $this->app->make(PermissionRegistry::class)->applyPolicies();
        });
    }

    protected function registerMigrations(): void
    {
        if (Permissions::$runs_migrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'permissions-migrations');

        $this->publishes([
            __DIR__ . '/../config/permissions.php' => config_path('permissions.php'),
        ], 'permissions-config');

        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
        ], 'permissions-spatie-config');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/permissions'),
        ], 'permissions-translations');
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('anypermission', function (string|array $permissions, ?string $guard = null) {
            return $this->app->make(PermissionRegistry::class)->check($permissions, $guard);
        });
    }

    /**
     * Log role and permission changes when spatie/laravel-activitylog is installed
     */
    protected function registerActivityLogging(): void
    {
        if (! config('permissions.log_activity', true)) {
            return;
        }

        if (! function_exists('activity')) {
            return;
        }

        Event::listen(UserRoleUpdated::class, LogUserRoleUpdate::class);
        Event::listen(RolePermissionsUpdated::class, LogRolePermissionsUpdate::class);
    }
}
