<?php

namespace Javaabu\Permissions\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RolePermissionsUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $old_permissions
     * @param  array<int, string>  $new_permissions
     */
    public function __construct(
        public array $old_permissions,
        public array $new_permissions,
        public Model $role,
        public ?Model $causer = null,
    ) {}
}
