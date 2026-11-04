<?php

namespace Javaabu\Permissions\Sync;

use Javaabu\Permissions\Registry\PermissionRegistry;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncer
{
    public function __construct(
        protected PermissionRegistry $registry,
        protected PermissionRegistrar $permission_registrar,
    ) {}

    /**
     * Create or update the registered permissions and roles, and grant the default permissions
     *
     * @param  array<int, string>|null  $only_guards  limit the sync to these guards
     * @param  bool  $prune  delete permissions in registered groups that are no longer registered
     */
    public function sync(?array $only_guards = null, bool $prune = false): SyncResult
    {
        $result = new SyncResult;

        $this->permission_registrar->forgetCachedPermissions();

        $this->syncPermissions($result, $only_guards, $prune);
        $this->syncRoles($result, $only_guards);
        $this->syncGrants($result, $only_guards);

        $this->permission_registrar->forgetCachedPermissions();

        return $result;
    }

    protected function syncPermissions(SyncResult $result, ?array $only_guards, bool $prune): void
    {
        $permission_class = $this->permissionClass();

        foreach ($this->registry->groups() as $group) {
            foreach ($this->filterGuards($this->registry->guardsFor($group), $only_guards) as $guard) {
                foreach ($group->getPermissions() as $name => $description) {
                    $permission = $permission_class::query()->firstOrNew([
                        'name' => $name,
                        'guard_name' => $guard,
                    ]);

                    $exists = $permission->exists;

                    $permission->forceFill([
                        'description' => $description,
                        'model' => $group->name,
                    ]);

                    if (! $permission->isDirty()) {
                        continue;
                    }

                    $permission->save();

                    $exists
                        ? $result->updated_permissions[] = "{$guard}:{$name}"
                        : $result->created_permissions[] = "{$guard}:{$name}";
                }

                if (! $prune) {
                    continue;
                }

                $stale = $permission_class::query()
                    ->where('guard_name', $guard)
                    ->where('model', $group->name)
                    ->whereNotIn('name', array_keys($group->getPermissions()))
                    ->get();

                foreach ($stale as $permission) {
                    $result->pruned_permissions[] = "{$guard}:{$permission->name}";

                    $permission->delete();
                }
            }
        }
    }

    protected function syncRoles(SyncResult $result, ?array $only_guards): void
    {
        $role_class = $this->roleClass();
        $permission_class = $this->permissionClass();

        foreach ($this->registry->roles() as $definition) {
            foreach ($this->filterGuards($this->registry->guardsFor($definition), $only_guards) as $guard) {
                $role = $role_class::query()->firstOrNew([
                    'name' => $definition->name,
                    'guard_name' => $guard,
                ]);

                $exists = $role->exists;

                $role->forceFill(['description' => $definition->getDescription()]);

                if ($role->isDirty()) {
                    $role->save();

                    $exists
                        ? $result->updated_roles[] = "{$guard}:{$definition->name}"
                        : $result->created_roles[] = "{$guard}:{$definition->name}";
                }

                $available = $permission_class::query()
                    ->where('guard_name', $guard)
                    ->pluck('name')
                    ->all();

                $permissions = $definition->resolvePermissions($available);

                $definition->grantsEverything()
                    ? $role->syncPermissions($permissions)
                    : $role->givePermissionTo($permissions);
            }
        }
    }

    protected function syncGrants(SyncResult $result, ?array $only_guards): void
    {
        $role_class = $this->roleClass();

        foreach ($this->registry->groups() as $group) {
            foreach ($group->getGrants() as $role_name => $permissions) {
                foreach ($this->filterGuards($this->registry->guardsFor($group), $only_guards) as $guard) {
                    $role = $role_class::query()
                        ->where('name', $role_name)
                        ->where('guard_name', $guard)
                        ->first();

                    if (! $role) {
                        $result->skipped_grants[] = "{$guard}:{$role_name}";

                        continue;
                    }

                    $role->givePermissionTo($permissions);
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $guards
     * @param  array<int, string>|null  $only_guards
     * @return array<int, string>
     */
    protected function filterGuards(array $guards, ?array $only_guards): array
    {
        if (! $only_guards) {
            return $guards;
        }

        return array_values(array_intersect($guards, $only_guards));
    }

    /** @return class-string<PermissionContract> */
    protected function permissionClass(): string
    {
        return $this->permission_registrar->getPermissionClass();
    }

    /** @return class-string<RoleContract> */
    protected function roleClass(): string
    {
        return $this->permission_registrar->getRoleClass();
    }
}
