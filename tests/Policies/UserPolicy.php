<?php

namespace Javaabu\Permissions\Tests\Policies;

use Javaabu\Permissions\Tests\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->name == 'John';
    }

    public function updateRole(User $user): bool
    {
        return $user->name == 'John';
    }
}
