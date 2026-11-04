<?php

namespace Javaabu\Permissions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Javaabu\Permissions\Database\Factories\RoleFactory;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Spatie\Permission\Models\Role as BaseRole;
use Spatie\Permission\PermissionRegistrar;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $description
 */
class Role extends BaseRole
{
    use HasFactory;

    /** @var array<int, string> */
    protected array $searchable = [
        'name',
        'description',
    ];

    protected static function newFactory(): Factory
    {
        return RoleFactory::new();
    }

    /**
     * Roles of a guard
     */
    public function scopeForGuard(Builder $query, string $guard): Builder
    {
        return $query->where('guard_name', $guard);
    }

    /**
     * Roles whose name or description contains the search term
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            foreach ($this->searchable as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * The description, or the name when there's none, translated
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return __($this->description ?: $this->name);
    }

    /**
     * Replace the role's permissions with the given permission ids of the role's guard,
     * and dispatch RolePermissionsUpdated if they changed
     *
     * @param  array<int, int|string>  $permission_ids
     */
    public function syncPermissionIds(array $permission_ids, ?Model $causer = null): void
    {
        $permission_class = app(PermissionRegistrar::class)->getPermissionClass();

        $old_permissions = $this->permissions()->pluck('name')->all();

        $new_permission_ids = $permission_class::query()
            ->where('guard_name', $this->guard_name)
            ->whereIn('id', $permission_ids)
            ->pluck('id')
            ->all();

        $this->permissions()->sync($new_permission_ids);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->unsetRelation('permissions');

        $new_permissions = $this->permissions()->pluck('name')->all();

        if (! array_diff($old_permissions, $new_permissions) && ! array_diff($new_permissions, $old_permissions)) {
            return;
        }

        RolePermissionsUpdated::dispatch($old_permissions, $new_permissions, $this, $causer ?? auth()->user());
    }
}
