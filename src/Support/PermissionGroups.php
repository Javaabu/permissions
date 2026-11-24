<?php

namespace Javaabu\Permissions\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Javaabu\Permissions\Registry\PermissionRegistry;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions grouped and labelled for display, e.g. on the role form
 */
class PermissionGroups
{
    public function __construct(
        protected PermissionRegistry $registry,
        protected PermissionRegistrar $permission_registrar,
    ) {}

    /**
     * Every permission of the guard, grouped, marking those the role has
     *
     * @return array<int, array{
     *     name: string,
     *     label: string,
     *     permissions: array<int, array{id: int, name: string, description: string, granted: bool}>
     * }>
     */
    public function forGuard(string $guard, ?Model $role = null): array
    {
        $permissions = $this->permission_registrar->getPermissionClass()::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->get();

        $granted = $role
            ? $role->permissions()->pluck('id')->all()
            : [];

        return $this->group($permissions, $granted);
    }

    /**
     * Only the permissions the role has, grouped
     *
     * @return array<int, array{
     *     name: string,
     *     label: string,
     *     permissions: array<int, array{id: int, name: string, description: string, granted: bool}>
     * }>
     */
    public function forRole(Model $role): array
    {
        $permissions = $role->permissions()->orderBy('name')->get();

        return $this->group($permissions, $permissions->pluck('id')->all());
    }

    /**
     * Groups in registration order, then unregistered groups alphabetically, then ungrouped permissions
     *
     * @param  array<int, int>  $granted
     * @return array<int, array<string, mixed>>
     */
    protected function group(Collection $permissions, array $granted): array
    {
        $order = array_flip(array_keys($this->registry->groups()));

        return $permissions
            ->groupBy(fn (Model $permission) => (string) $permission->model)
            ->sortBy(fn ($items, string $group) => match (true) {
                $group === '' => [2, ''],
                isset($order[$group]) => [0, sprintf('%05d', $order[$group])],
                default => [1, $group],
            })
            ->map(fn (Collection $items, string $group) => [
                'name' => $group,
                'label' => $group === '' ? __('Other') : __($this->registry->label($group)),
                'permissions' => $this->sortWithinGroup($items, $group)->map(fn (Model $permission) => [
                    'id' => $permission->getKey(),
                    'name' => $permission->name,
                    'description' => __($permission->description ?: $permission->name),
                    'granted' => in_array($permission->getKey(), $granted),
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Registered permissions in the order they were registered, then the rest by name
     */
    protected function sortWithinGroup(Collection $items, string $group): Collection
    {
        $order = array_flip(array_keys($this->registry->group($group)?->getPermissions() ?? []));

        return $items->sortBy(fn (Model $permission) => [
            $order[$permission->name] ?? PHP_INT_MAX,
            $permission->name,
        ]);
    }
}
