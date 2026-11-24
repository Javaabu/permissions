<?php

namespace Javaabu\Permissions\Tests\Policies;

use Illuminate\Database\Eloquent\Model;

class AppPostPolicy
{
    public function viewAny(Model $user): bool
    {
        return true;
    }
}
