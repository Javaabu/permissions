<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Javaabu\Activitylog\Models\Activity;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\Policies\UserPolicy;
use Javaabu\Permissions\Tests\TestCase;

class RolesTest extends TestCase
{
    use InteractsWithDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /** @test */
    public function it_can_sync_the_role_permissions(): void
    {
        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role->save();

        $permission = new Permission([
            'name' => 'test',
            'description' => 'Test description',
            'guard_name' => 'web',
            'model' => 'test'
        ]);

        $permission->save();

        $role->syncPermissionIds([$permission->id]);

        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    /** @test */
    function an_event_is_emitted_when_a_roles_permissions_is_updated()
    {
        Event::fake();

        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role->save();

        $permission = new Permission([
            'name' => 'test',
            'description' => 'Test description',
            'guard_name' => 'web',
            'model' => 'test'
        ]);

        $permission->save();

        $this->actingAs($user);

        $role->syncPermissionIds([$permission->id]);

        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        Event::assertDispatched(RolePermissionsUpdated::class, function ($event) use ($user, $role, $permission) {
            return $event->old_permissions == [] && $event->new_permissions == [$permission->name] && $event->causer == $user && $event->role == $role;
        });
    }

    /** @test */
    function it_logs_the_new_and_old_permissions_when_a_role_is_updated()
    {
        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role->save();

        $permission = new Permission([
            'name' => 'test',
            'description' => 'Test description',
            'guard_name' => 'web',
            'model' => 'test'
        ]);

        $permission->save();

        $this->actingAs($user);

        $role->syncPermissionIds([$permission->id]);

        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        /** @var Activity $log */
        $log = $role->activities()->latest('id')->first();
        $changes = $log->changes();

        $old = $changes->get('old');
        $new = $changes->get('attributes');

        $this->assertDatabaseHas('activity_log', [
            'id' => $log->id,
            'description' => 'permissions_updated',
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        $this->assertArrayHasKey('permissions', $new);
        $this->assertEquals([$permission->name], $new['permissions']);

        $this->assertArrayHasKey('permissions', $old);
        $this->assertEmpty($old['permissions']);
    }
}
