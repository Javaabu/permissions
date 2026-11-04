<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ModelsTest extends TestCase
{
    use InteractsWithDatabase;

    #[Test]
    public function permissions_translate_their_description_and_group_label(): void
    {
        __('Other', locale: 'dv');
        $this->app['translator']->addLines(['*.View exports' => 'އެކްސްޕޯޓްތައް ބެލުން', '*.Exports' => 'އެކްސްޕޯޓްތައް'], 'dv');
        $this->app->setLocale('dv');

        Permissions::register('exports', ['view_exports' => 'View exports'], label: 'Exports');
        Permissions::sync();

        $permission = Permission::findByName('view_exports');
        $manual = Permission::query()->create(['name' => 'manual_permission', 'guard_name' => 'web']);

        $this->assertEquals('އެކްސްޕޯޓްތައް ބެލުން', $permission->translated_description);
        $this->assertEquals('އެކްސްޕޯޓްތައް', $permission->group_label);
        $this->assertEquals('manual_permission', $manual->translated_description);
        $this->assertEquals('އެހެނިހެން', $manual->group_label);
    }

    #[Test]
    public function roles_can_be_searched_and_translate_their_description(): void
    {
        $this->app->setLocale('dv');

        Permissions::sync();

        Role::factory()->create(['name' => 'editor', 'description' => 'Content editor', 'guard_name' => 'web']);

        $this->assertEquals(['editor'], Role::query()->search('content')->pluck('name')->all());
        $this->assertEquals(['super_admin'], Role::query()->search('super')->pluck('name')->all());
        $this->assertEquals(2, Role::query()->search(null)->count());
        $this->assertEquals('ސުޕަރ އެޑްމިން', Role::findByName('super_admin')->translated_description);
    }

    #[Test]
    public function factories_make_permissions_and_roles(): void
    {
        $permission = Permission::factory()->create(['guard_name' => 'web']);

        $this->assertNotEmpty($permission->model);
        $this->assertDatabaseHas('permissions', ['id' => $permission->id]);
    }

    #[Test]
    public function a_new_user_gets_the_default_role_when_the_causer_cannot_choose_one(): void
    {
        Gate::before(fn () => false);
        config()->set('permissions.default_role', 'member');

        Role::query()->create(['name' => 'member', 'guard_name' => 'web']);
        Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);

        $causer = User::query()->create(['name' => 'އަޙްމަދު', 'email' => 'ahmed@example.com']);
        $user = User::query()->create(['name' => 'މަރިޔަމް', 'email' => 'mariyam@example.com']);
        $existing = User::query()->create(['name' => 'ޢަލީ', 'email' => 'ali@example.com']);

        $user->updateRole('admin', new: true, causer: $causer);
        $existing->updateRole('admin', causer: $causer);

        $this->assertEquals(['member'], $user->roles()->pluck('name')->all());
        $this->assertEmpty($existing->roles()->pluck('name')->all());
    }

    #[Test]
    public function a_causer_that_may_update_roles_can_remove_them_all(): void
    {
        Gate::before(fn () => true);

        Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::query()->create(['name' => 'މަރިޔަމް', 'email' => 'mariyam@example.com']);
        $user->assignRole('admin');

        $user->updateRole([], causer: $user);

        $this->assertEmpty($user->roles()->pluck('name')->all());
    }

    #[Test]
    public function it_finds_models_whose_roles_have_all_the_permissions(): void
    {
        Permissions::resource('exports');
        Permissions::role('viewer', permissions: ['view_exports']);
        Permissions::sync();

        $admin = User::query()->create(['name' => 'އަޙްމަދު', 'email' => 'ahmed@example.com']);
        $viewer = User::query()->create(['name' => 'މަރިޔަމް', 'email' => 'mariyam@example.com']);

        $admin->assignRole('super_admin');
        $viewer->assignRole('viewer');

        $this->assertEqualsCanonicalizing([$admin->id, $viewer->id], User::query()->whereRoleHasPermissions('view_exports')->pluck('id')->all());
        $this->assertEquals([$admin->id], User::query()->whereRoleHasPermissions(['view_exports', 'edit_exports'])->pluck('id')->all());
    }
}
