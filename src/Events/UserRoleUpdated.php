<?php

namespace Javaabu\Permissions\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;

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
     * @var string|array
     */
    public $old_role;

    /**
     * @var string|array
     */
    public $new_role;

    /**
     * Create a new event instance.
     *
     * @param string|array $old_role
     * @param string|array $new_role
     * @param $subject
     * @param null $causer
     */
    public function __construct($old_role, $new_role, $subject, $causer = null)
    {
        $this->old_role = Arr::wrap($old_role);
        $this->new_role = Arr::wrap($new_role);
        $this->subject = $subject;
        $this->causer = $causer;
    }
}
