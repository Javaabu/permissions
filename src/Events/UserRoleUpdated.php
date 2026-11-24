<?php

namespace Javaabu\Permissions\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class UserRoleUpdated
{
    use Dispatchable;
    use SerializesModels;

    /** @var array<int, string> */
    public array $old_role;

    /** @var array<int, string> */
    public array $new_role;

    /**
     * @param  string|array<int, string>  $old_role
     * @param  string|array<int, string>  $new_role
     */
    public function __construct(
        string|array $old_role,
        string|array $new_role,
        public Model $subject,
        public ?Model $causer = null,
    ) {
        $this->old_role = Arr::wrap($old_role);
        $this->new_role = Arr::wrap($new_role);
    }
}
