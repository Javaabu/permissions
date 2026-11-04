<?php

namespace Javaabu\Permissions\Database\Seeders;

use Illuminate\Database\Seeder;
use Javaabu\Permissions\Sync\PermissionSyncer;

class PermissionsSeeder extends Seeder
{
    /**
     * Create or update the registered permissions and roles
     */
    public function run(PermissionSyncer $syncer): void
    {
        $syncer->sync();
    }
}
