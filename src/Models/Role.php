<?php

namespace Javaabu\Permissions\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole implements AdminModel
{
    use HasFactory;
    use IsAdminModel;

    // TODO: Add LogsActivity trait from javaabu/activitylog package
//    use LogsActivity;

    /**
     * The attributes that would be logged
     *
     * @var array
     */
//    protected static $logAttributes = ['*'];

    /**
     * Log only changed attributes
     *
     * @var boolean
     */
//    protected static $logOnlyDirty = true;

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
     * A search scope
     *
     * @param $query
     * @param $search
     * @return
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%'.$search.'%')
                     ->orWhere('description', 'like', '%'.$search.'%');
    }

    /**
     * Get the admin url attribute
     */
    public function getAdminUrlAttribute(): string
    {
        return action([RolesController::class, 'show'], $this);
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

    /*public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(static::$logAttributes);
    }*/
}
