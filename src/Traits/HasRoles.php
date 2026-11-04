<?php

namespace Javaabu\Permissions\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles as SpatieHasRoles;

trait HasRoles
{
    use SpatieHasRoles;

    /**
     * Whether the model has any of the permissions
     *
     * Without a guard, this goes through the gate, so `Gate::before()` callbacks apply.
     * With a guard, only permissions of that guard count.
     *
     * @param  string|array<int, string>  $permissions
     */
    public function anyPermission(string|array $permissions, ?string $guard = null): bool
    {
        foreach (Arr::wrap($permissions) as $permission) {
            $allowed = $guard === null
                ? $this->can($permission)
                : $this->checkPermissionTo($permission, $guard);

            if ($allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Change the model's roles, if the causer may, and dispatch UserRoleUpdated
     *
     * The causer may when it passes the `updateRole` ability for this model class.
     * When it doesn't and `$new` is true, the model gets the default role instead
     * (`$default_role`, or `config('permissions.default_role')`). Roles that don't
     * exist for the model's guard are ignored; if none of the given roles exist,
     * nothing changes. A causer that may update roles can pass `[]` to remove them all.
     *
     * @param  string|array<int, string>  $role
     * @param  bool  $new  whether the model is being created
     * @param  Model|null  $causer  defaults to the signed-in user
     */
    public function updateRole(string|array $role, bool $new = false, ?Model $causer = null, ?string $default_role = null): void
    {
        $causer ??= auth()->user();

        if (! $causer instanceof Model) {
            return;
        }

        $can_update_role = $causer->can('updateRole', static::class);

        if (! $can_update_role && ! $new) {
            return;
        }

        $new_role = $can_update_role
            ? Arr::wrap($role)
            : array_filter([$default_role ?? config('permissions.default_role')]);

        if ($new_role) {
            $role_class = app(PermissionRegistrar::class)->getRoleClass();

            $existing_roles = $role_class::query()
                ->where('guard_name', $this->getDefaultGuardName())
                ->whereIn('name', $new_role)
                ->pluck('name')
                ->all();

            $new_role = array_values(array_intersect($new_role, $existing_roles));

            if (! $new_role) {
                return;
            }
        }

        if (! $new_role && ! $can_update_role) {
            return;
        }

        $old_role = $this->roles()->pluck('name')->all();

        $this->syncRoles($new_role);

        if (! array_diff($old_role, $new_role) && ! array_diff($new_role, $old_role)) {
            return;
        }

        UserRoleUpdated::dispatch($old_role, $new_role, $this, $causer);
    }

    /**
     * The model's first role
     */
    public function getRoleAttribute(): ?Model
    {
        return $this->roles->first();
    }

    /**
     * Models that have the permission through any of their roles, on any guard
     *
     * Unlike spatie's `permission()` scope, this ignores direct permissions.
     *
     * @param  string|array<int, string>  $permissions  all are required
     */
    public function scopeWhereRoleHasPermissions(Builder $query, string|array $permissions): Builder
    {
        return $query->whereHas('roles', function (Builder $query) use ($permissions) {
            foreach (Arr::wrap($permissions) as $permission) {
                $query->whereHas('permissions', fn (Builder $query) => $query->where('name', $permission));
            }
        });
    }
}
