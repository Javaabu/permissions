<?php

namespace Javaabu\Permissions\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Models\Role;

trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    /**
     * Check if the user has any of the following permissions
     *
     * @param  array|string  $permissions
     */
    public function anyPermission($permissions): bool
    {
        if (! is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the role
     *
     * @param $role
     * @param bool $new whether creating user for the first time
     */
    public function updateRole($role, bool $new = false)
    {
        $user = auth()->user();
        if (! $user instanceof Model) {
            return;
        }

        $role = Arr::wrap($role);

        // update role only if it's a new user or the user can update the role
        if ($user->can('updateRole', self::class) || $new) {
            $old_role = $this->roles()->pluck('name')->all();
            $new_role = $user->can('updateRole', self::class) ? $role : [get_setting('default_role')];

            // remove fake roles
            if ($new_role) {
                $correct_role_names = Role::whereIn('name', $new_role)->pluck('name')->all();
                $correct_roles = array_intersect($new_role, $correct_role_names);

                // no correct rules
                if (! $correct_roles) {
                    return;
                }

                $new_role = $correct_roles;
            }

            $this->syncRoles($new_role);

            $same_roles = count($old_role) == count($new_role) && ! array_diff($old_role, $new_role);

            // log the role update
            if (! $same_roles) {
                event(new UserRoleUpdated($old_role, $new_role, $this, $user));
            }
        }
    }

    /**
     * Get all users with a specific permission
     *
     * @param $query
     * @param $permissions array|string
     * @return mixed
     */
    public function scopePermission($query, $permissions): mixed
    {
        if (! is_array($permissions)) {
            $permissions = [$permissions];
        }

        $query->whereHas('roles', function ($query) use ($permissions) {
            foreach ($permissions as $permission) {
                $query->whereHas('permissions', function ($query) use ($permission) {
                    $query->whereName($permission);
                });
            }
        });

        return $query;
    }

    /**
     * Get role attribute
     *
     * @return Role|null
     */
    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
