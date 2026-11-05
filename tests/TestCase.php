<?php

namespace Javaabu\Permissions\Tests;

use Illuminate\Support\Collection;
use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Manifest\ManifestServiceProvider;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\PermissionsServiceProvider;
use Javaabu\Permissions\Registry\PermissionRegistry;
use Javaabu\Permissions\Tests\Models\Admin;
use Javaabu\Permissions\Tests\Models\User;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /** Start each test with nothing registered */
    protected bool $flush_registry = true;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->flush_registry) {
            $this->app->make(PermissionRegistry::class)->flush();
        }
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');
        $app['config']->set('session.serialization', 'php');
        $app['config']->set('permission.models.permission', Permission::class);
        $app['config']->set('permission.models.role', Role::class);

        $app['config']->set('auth.guards', [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'web_admin' => [
                'driver' => 'session',
                'provider' => 'admins',
            ],
        ]);

        $app['config']->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent',
                'model' => User::class,
            ],
            'admins' => [
                'driver' => 'eloquent',
                'model' => Admin::class,
            ],
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActivitylogServiceProvider::class,
            PermissionServiceProvider::class,
            PermissionsServiceProvider::class,
            ManifestServiceProvider::class,
            FormsServiceProvider::class,
        ];
    }

    /**
     * The logged before and after values: `attribute_changes` on spatie/laravel-activitylog v5,
     * `properties` on v4
     */
    protected function loggedChanges(Activity $log): Collection
    {
        if (method_exists($log, 'changes')) {
            return $log->changes();
        }

        $this->assertNull($log->properties?->get('attributes'), 'v5 keeps the changes out of properties');

        return $log->attribute_changes;
    }
}
