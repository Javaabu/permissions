<?php

namespace Javaabu\Permissions\Listeners;

use Javaabu\Permissions\Events\RolePermissionsUpdated;

class LogRolePermissionsUpdate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RolePermissionsUpdated  $event
     * @return void
     */
    public function handle(RolePermissionsUpdated $event)
    {
        $log = activity()->performedOn($event->role);

        if ($user = $event->causer) {
            $log->causedBy($user);
        }

        $log->withProperties([
            'attributes' => ['permissions' => $event->new_permissions],
            'old' => ['permissions' => $event->old_permissions]
        ])
        ->log('permissions_updated');
    }
}
