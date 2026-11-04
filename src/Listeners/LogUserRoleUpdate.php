<?php

namespace Javaabu\Permissions\Listeners;

use Javaabu\Permissions\Events\UserRoleUpdated;

class LogUserRoleUpdate
{
    public function handle(UserRoleUpdated $event): void
    {
        $log = activity()->performedOn($event->subject);

        if ($event->causer) {
            $log->causedBy($event->causer);
        }

        $log->withProperties([
            'attributes' => ['role' => $event->new_role],
            'old' => ['role' => $event->old_role],
        ])->log('role_updated');
    }
}
