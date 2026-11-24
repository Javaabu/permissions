<?php

namespace Javaabu\Permissions\Policies;

use Illuminate\Database\Eloquent\Model;

/**
 * Roles: view_roles, edit_roles and delete_roles
 */
class RolePolicy extends ResourcePolicy
{
    protected string $resource = 'roles';

    /**
     * Roles aren't soft deleted
     */
    public function restore(Model $user, Model $model): bool
    {
        return false;
    }

    public function forceDelete(Model $user, Model $model): bool
    {
        return false;
    }

    public function viewTrash(Model $user): bool
    {
        return false;
    }
}
