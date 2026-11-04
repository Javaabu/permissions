<?php

namespace Javaabu\Permissions\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Javaabu\Permissions\Models\Role;

class RolePermissionsUpdated
{
    use Dispatchable;
    use SerializesModels;

    public Role $role;

    public ?User $causer;

    public array $old_permissions;

    public array $new_permissions;

    /**
     * Create a new event instance.
     */
    public function __construct(array $old_permissions, array $new_permissions, Role $role, ?User $causer = null)
    {
        $this->old_permissions = $old_permissions;
        $this->new_permissions = $new_permissions;
        $this->role = $role;
        $this->causer = $causer;
    }
}
