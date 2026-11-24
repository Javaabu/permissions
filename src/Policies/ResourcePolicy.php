<?php

namespace Javaabu\Permissions\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Javaabu\Permissions\Registry\PermissionRegistry;

/**
 * A policy whose abilities map to the conventional permissions of a resource
 *
 * | Ability            | Permission                                                    |
 * | ------------------ | ------------------------------------------------------------- |
 * | viewAny, view      | view_{resource}                                               |
 * | create             | edit_{resource}                                               |
 * | update             | edit_{resource}                                               |
 * | delete             | delete_{resource}                                             |
 * | restore, viewTrash | delete_{resource} or force_delete_{resource}                  |
 * | forceDelete        | force_delete_{resource}                                       |
 *
 * With `$owned = true`, update and delete need `edit_others_{resource}` / `delete_others_{resource}`,
 * or `edit_{resource}` / `delete_{resource}` and owning the model (see `isOwner()`).
 *
 * Custom abilities go in `$abilities`: `['approve' => 'approve_exports']`. A list of
 * permissions means the user needs any of them.
 */
abstract class ResourcePolicy
{
    /** The resource part of the permission names, e.g. `exports` for `view_exports` */
    protected string $resource;

    /** Check permissions of this guard instead of going through the gate */
    protected ?string $guard = null;

    /** Whether update and delete distinguish the user's own models from others' */
    protected bool $owned = false;

    /** @var array<string, string|array<int, string>> ability => permission(s) */
    protected array $abilities = [];

    public function viewAny(Model $user): bool
    {
        return $this->allows($user, $this->permission('view'));
    }

    public function view(Model $user, Model $model): bool
    {
        if ($this->allows($user, $this->permission('view'))) {
            return true;
        }

        return $this->owned && $this->update($user, $model);
    }

    public function create(Model $user): bool
    {
        return $this->allows($user, $this->permission('edit'));
    }

    public function update(Model $user, Model $model): bool
    {
        return $this->allowsOwned($user, $model, 'edit');
    }

    public function delete(Model $user, Model $model): bool
    {
        return $this->allowsOwned($user, $model, 'delete');
    }

    public function restore(Model $user, Model $model): bool
    {
        return $this->delete($user, $model) || $this->forceDelete($user, $model);
    }

    public function forceDelete(Model $user, Model $model): bool
    {
        return $this->allows($user, $this->permission('force_delete'));
    }

    public function viewTrash(Model $user): bool
    {
        return $this->allows($user, $this->permission('delete'), $this->permission('force_delete'));
    }

    /**
     * Abilities from `$abilities`. Unknown abilities are denied.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $ability, array $arguments): bool
    {
        $user = $arguments[0] ?? null;

        if (! $user instanceof Model) {
            return false;
        }

        if (! array_key_exists($ability, $this->abilities)) {
            return false;
        }

        return $this->allows($user, ...Arr::wrap($this->abilities[$ability]));
    }

    /**
     * The permission name for an action on this policy's resource
     */
    public function permission(string $action): string
    {
        return PermissionRegistry::permissionName($action, $this->resource);
    }

    /**
     * Whether the user has any of the permissions
     */
    protected function allows(Model $user, string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            $allowed = $this->guard !== null && method_exists($user, 'checkPermissionTo')
                ? $user->checkPermissionTo($permission, $this->guard)
                : $user->can($permission);

            if ($allowed) {
                return true;
            }
        }

        return false;
    }

    protected function allowsOwned(Model $user, Model $model, string $action): bool
    {
        if (! $this->owned) {
            return $this->allows($user, $this->permission($action));
        }

        if ($this->allows($user, $this->permission("{$action}_others"))) {
            return true;
        }

        return $this->allows($user, $this->permission($action)) && $this->isOwner($user, $model);
    }

    /**
     * Whether the user owns the model. Calls `$model->isOwnedBy($user)` when the model has it;
     * override for anything else.
     */
    protected function isOwner(Model $user, Model $model): bool
    {
        if (method_exists($model, 'isOwnedBy')) {
            return (bool) $model->isOwnedBy($user);
        }

        return false;
    }
}
