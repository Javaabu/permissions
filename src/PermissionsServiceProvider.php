<?php

namespace Javaabu\Permissions;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Javaabu\Manifest\Concerns\RegistersAssets;
use Javaabu\Manifest\Package;
use Javaabu\Permissions\Commands\SyncPermissionsCommand;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Listeners\LogRolePermissionsUpdate;
use Javaabu\Permissions\Listeners\LogUserRoleUpdate;
use Javaabu\Permissions\Policies\RolePolicy;
use Javaabu\Permissions\Registry\PermissionRegistry;
use Javaabu\Permissions\Routing\RoleRoutes;
use Spatie\Permission\PermissionRegistrar;

class PermissionsServiceProvider extends ServiceProvider
{
    use RegistersAssets;

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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'permissions');

        $this->registerFrontendAssets();
        $this->registerRouteMacros();
        $this->registerDefaultPermissions();

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
            __DIR__ . '/../resources/views' => resource_path('views/vendor/permissions'),
        ], 'permissions-views');
    }

    /**
     * The Inertia pages (permissions::Roles/Index, …), the components apps can import as
     * `@javaabu/permissions`, and the script the Blade pages load
     */
    protected function registerFrontendAssets(): void
    {
        $this->registerAssets('permissions', __DIR__ . '/../resources', function (Package $package) {
            $package
                ->script('roles', 'js/roles.ts')
                ->js()
                ->pages()
                // roles.ts (the Blade script) imports nothing; only the Inertia pages and the
                // components apps import from @javaabu/permissions need these, so Blade apps
                // aren't asked to install them. Onu is a peer of @javaabu/forms.
                ->npm([
                    '@javaabu/forms' => 'github:Javaabu/react-forms#next-gen',
                    '@javaabu/onu' => '^0.2.1',
                ], peer_dependencies: [
                    'react' => '^18.3 || ^19.0',
                    'react-dom' => '^18.3 || ^19.0',
                    '@inertiajs/react' => '^2.0 || ^3.0',
                ], for: 'pages');
        });
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('roles', function (array $options = []) {
            RoleRoutes::register($this, $options);
        });
    }

    /**
     * The package's own permissions (view_roles, edit_roles, delete_roles) and role policy
     */
    protected function registerDefaultPermissions(): void
    {
        if (! config('permissions.register_defaults', true)) {
            return;
        }

        $registry = $this->app->make(PermissionRegistry::class);

        $registry->resource('roles', 'roles', ['view', 'edit', 'delete'], label: 'Roles');
        $registry->policy($this->app->make(PermissionRegistrar::class)->getRoleClass(), RolePolicy::class);
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
