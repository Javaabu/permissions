<?php

namespace Javaabu\Permissions\Listeners;

use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Listeners\Concerns\LogsChanges;

class LogUserRoleUpdate
{
    use LogsChanges;

    public function handle(UserRoleUpdated $event): void
    {
        $log = activity()->performedOn($event->subject);

        if ($event->causer) {
            $log->causedBy($event->causer);
        }

        $this->withChanges(
            $log,
            ['role' => $event->new_role],
            ['role' => $event->old_role],
        )->log('role_updated');
    }
}
