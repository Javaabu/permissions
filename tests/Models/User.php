<?php

namespace Javaabu\Permissions\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Javaabu\Activitylog\Traits\LogsActivity;
use Javaabu\Permissions\Traits\HasRoles;
use Spatie\Activitylog\Traits\CausesActivity;

class User extends Authenticatable
{
    use HasRoles;
    use LogsActivity;
    use CausesActivity;

    protected $guarded = [];
}
