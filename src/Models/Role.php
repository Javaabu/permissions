<?php

namespace Javaabu\Permissions\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Activitylog\Traits\LogsActivity;
use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole implements AdminModel
{
    use HasFactory;
    use IsAdminModel;
    use LogsActivity;

    /**
     * The attributes that would be logged
     *
     * @var array
     */
    protected static array $logAttributes = ['*'];

    /**
     * Searchable fields
     */
    protected array $searchable = [
        'name',
        'description',
    ];

    /**
     * Create a new factory instance for the role model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return \Database\Factories\RoleFactory::new();
    }

    /**
     * Get the admin url attribute
     */
    public function getAdminUrlAttribute(): string
    {
        return route('admin.roles.show', $this);
    }

    /**
     * Get name attribute
     * @return string
     */
    public function getAdminLinkNameAttribute(): string
    {
        return $this->description;
    }

    /**
     * Sync permissions
     *
     * @param  array  $permissions
     */
    public function syncPermissionIds(array $permissions): void
    {
        $old_permissions = $this->permissions()->pluck('name');
        $new_permissions = Permission::whereIn('id', $permissions)->pluck('name');

        $this->permissions()->sync($permissions);

        $user = auth()->user();
        event(new RolePermissionsUpdated(
            $old_permissions->all(),
            $new_permissions->all(),
            $this,
            $user
        ));
    }
}
