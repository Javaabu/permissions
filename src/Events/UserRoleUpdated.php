<?php

namespace Javaabu\Permissions\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserRoleUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var App\Models\User
     */
    public $subject;

    /**
     * @var App\Models\User
     */
    public $causer;

    /**
     * @var string
     */
    public $old_role;

    /**
     * @var string
     */
    public $new_role;

    /**
     * Create a new event instance.
     *
     * @param string $old_role
     * @param string $new_role
     * @param $subject
     * @param null $causer
     */
    public function __construct($old_role, $new_role, $subject, $causer = null)
    {
        $this->old_role = $old_role;
        $this->new_role = $new_role;
        $this->subject = $subject;
        $this->causer = $causer;
    }
}
