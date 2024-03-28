<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Javaabu\Activitylog\Models\Activity;
use Javaabu\Permissions\Events\UserRoleUpdated;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\Policies\UserPolicy;
use Javaabu\Permissions\Tests\TestCase;

class HasRolesTest extends TestCase
{
    use InteractsWithDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /** @test */
    public function it_can_assign_a_single_role(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role->save();

        $this->actingAs($user);

        $user->updateRole($role->name);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_can_assign_multiple_roles(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role_1 = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role_2 = new Role([
            'name' => 'Test Role 2',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role_1->save();
        $role_2->save();

        $this->actingAs($user);

        $user->updateRole([
            $role_1->name,
            $role_2->name,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role_1->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role_2->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    /** @test */
    function an_event_is_emitted_when_the_user_role_is_updated()
    {
        Event::fake();

        Gate::policy(User::class, UserPolicy::class);

        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role->save();

        $this->actingAs($user);

        $user->updateRole($role->name);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        Event::assertDispatched(UserRoleUpdated::class, function ($event) use ($user, $role) {
            return $event->old_role == [] && $event->new_role == [$role->name] && $event->causer == $user && $event->subject == $user;
        });
    }

    /** @test */
    function it_logs_the_new_and_old_roles_when_a_user_role_is_updated()
    {
        Gate::policy(User::class, UserPolicy::class);

        $user = new User(['name' => 'John', 'email' => 'test@example.com']);
        $user->save();

        $role = new Role([
            'name' => 'Test Role',
            'description' => 'Test description',
            'guard_name' => 'web'
        ]);

        $role->save();

        $this->actingAs($user);

        $user->updateRole($role->name);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        /** @var Activity $log */
        $log = $user->activities()->latest('id')->first();
        $changes = $log->changes();

        $old = $changes->get('old');
        $new = $changes->get('attributes');

        $this->assertDatabaseHas('activity_log', [
            'id' => $log->id,
            'description' => 'role_updated',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        $this->assertArrayHasKey('role', $new);
        $this->assertEquals([$role->name], $new['role']);

        $this->assertArrayHasKey('role', $old);
        $this->assertEmpty($old['role']);
    }
}
