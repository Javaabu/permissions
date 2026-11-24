<?php

namespace Javaabu\Permissions\Tests\Feature;

use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;

class ActivityLogDisabledTest extends TestCase
{
    use InteractsWithDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permissions.log_activity', false);
    }

    #[Test]
    public function it_does_not_log_permission_changes_when_activity_logging_is_off(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'view_exports', 'guard_name' => 'web']);

        $role->syncPermissionIds([$permission->id]);

        $this->assertDatabaseHas('role_has_permissions', ['role_id' => $role->id, 'permission_id' => $permission->id]);
        $this->assertEquals(0, Activity::query()->count());
    }
}
