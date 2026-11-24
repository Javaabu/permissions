<?php

namespace Javaabu\Permissions\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Javaabu\Permissions\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $guarded = [];
}
