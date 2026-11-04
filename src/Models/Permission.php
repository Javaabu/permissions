<?php

namespace Javaabu\Permissions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Permissions\Database\Factories\PermissionFactory;
use Javaabu\Permissions\Registry\PermissionRegistry;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $description
 * @property string|null $model the permission group
 */
class Permission extends SpatiePermission
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return PermissionFactory::new();
    }

    /**
     * Permissions of a guard
     */
    public function scopeForGuard(Builder $query, string $guard): Builder
    {
        return $query->where('guard_name', $guard);
    }

    /**
     * Permissions of a group
     */
    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('model', $group);
    }

    /**
     * The description, or the name when there's none, translated
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return __($this->description ?: $this->name);
    }

    /**
     * The translated label of the permission's group
     */
    public function getGroupLabelAttribute(): string
    {
        if (blank($this->model)) {
            return __('Other');
        }

        return __(app(PermissionRegistry::class)->label($this->model));
    }
}
