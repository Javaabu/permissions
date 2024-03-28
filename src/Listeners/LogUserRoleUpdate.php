<?php

namespace Javaabu\Permissions\Listeners;

use Javaabu\Permissions\Events\UserRoleUpdated;

class LogUserRoleUpdate
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
     * @param  UserRoleUpdated  $event
     * @return void
     */
    public function handle(UserRoleUpdated $event)
    {
        $log = activity()->performedOn($event->subject);

        if ($user = $event->causer) {
            $log->causedBy($user);
        }

        $log->withProperties([
            'attributes' => ['role' => $event->new_role],
            'old' => ['role' => $event->old_role]
        ])
        ->log('role_updated');
    }
}
