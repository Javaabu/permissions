<?php

namespace Javaabu\Permissions\Listeners;

use Javaabu\Permissions\Events\RolePermissionsUpdated;

class LogRolePermissionsUpdate
{
    public function handle(RolePermissionsUpdated $event): void
    {
        $log = activity()->performedOn($event->role);

        if ($event->causer) {
            $log->causedBy($event->causer);
        }

        $log->withProperties([
            'attributes' => ['permissions' => $event->new_permissions],
            'old' => ['permissions' => $event->old_permissions],
        ])->log('permissions_updated');
    }
}
