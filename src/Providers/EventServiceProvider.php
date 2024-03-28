<?php

namespace Javaabu\Permissions\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Listeners\LogRolePermissionsUpdate;
use Javaabu\Permissions\Listeners\LogUserRoleUpdate;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRoleUpdated::class => [
            LogUserRoleUpdate::class,
        ],

        RolePermissionsUpdated::class => [
            LogRolePermissionsUpdate::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
