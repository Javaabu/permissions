<?php

namespace Javaabu\Permissions\Listeners;

use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Javaabu\Permissions\Listeners\Concerns\LogsChanges;

class LogRolePermissionsUpdate
{
    use LogsChanges;

    public function handle(RolePermissionsUpdated $event): void
    {
        $log = activity()->performedOn($event->role);

        if ($event->causer) {
            $log->causedBy($event->causer);
        }

        $this->withChanges(
            $log,
            ['permissions' => $event->new_permissions],
            ['permissions' => $event->old_permissions],
        )->log('permissions_updated');
    }
}
