<?php

namespace Javaabu\Permissions\Tests;

use Javaabu\Activitylog\ActivitylogServiceProvider;
use Javaabu\Activitylog\Models\Activity;
use Javaabu\Helpers\HelpersServiceProvider;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\Models\User;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Javaabu\Permissions\PermissionsServiceProvider;

abstract class TestCase extends BaseTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        $this->app['config']->set('permission.models.permission', Permission::class);

        $this->app['config']->set('permission.models.role', Role::class);

        $this->app['config']->set('activitylog.activity_model', Activity::class);

        $this->app['config']->set('auth.guards', [
            'web' => [
                'driver' => 'session',
                'provider' => 'users'
            ]
        ]);

        $this->app['config']->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent',
                'model' => User::class,
            ]
        ]);

    }

    protected function getPackageProviders($app)
    {
        return [
            HelpersServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
            ActivitylogServiceProvider::class,
            \Spatie\Permission\PermissionServiceProvider::class,
            PermissionsServiceProvider::class
        ];
    }
}
