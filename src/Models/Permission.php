<?php

namespace Javaabu\Permissions\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    /**
     * Create a new factory instance for the role model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return \Database\Factories\PermissionFactory::new();
    }
}
