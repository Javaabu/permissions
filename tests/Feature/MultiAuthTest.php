<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\Models\Admin;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MultiAuthTest extends TestCase
{
    use InteractsWithDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('permissions.guards', ['web', 'web_admin']);

        Permissions::resource('exports');
        Permissions::register('reports', ['view_reports' => 'View reports'])->guards('web_admin');
        Permissions::role('auditor', 'Auditor', ['view_*']);

        Permissions::sync();
    }

    #[Test]
    public function roles_and_permissions_are_kept_per_guard(): void
    {
        $this->assertEquals(2, Role::query()->where('name', 'auditor')->count());
        $this->assertEquals(['view_exports'], Role::findByName('auditor', 'web')->permissions->pluck('name')->all());
        $this->assertEqualsCanonicalizing(
            ['view_exports', 'view_reports'],
            Role::findByName('auditor', 'web_admin')->permissions->pluck('name')->all(),
        );

        $this->assertEquals(['web_admin'], Role::query()->forGuard('web_admin')->where('name', 'auditor')->pluck('guard_name')->all());
        $this->assertEquals(5, Permission::query()->forGuard('web_admin')->count());
        $this->assertEquals(1, Permission::query()->forGuard('web_admin')->inGroup('reports')->count());
    }

    #[Test]
    public function each_user_model_gets_roles_of_its_own_guard(): void
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();

        $user->assignRole('auditor');
        $admin->assignRole('auditor');

        $this->assertEquals('web', Permissions::guardFor($user));
        $this->assertEquals('web_admin', Permissions::guardFor($admin));

        $this->assertTrue($admin->can('view_reports'));
        $this->assertFalse($user->can('view_reports'));
        $this->assertEquals('web_admin', $admin->role->guard_name);
    }

    #[Test]
    public function it_checks_permissions_against_a_specific_guard(): void
    {
        $admin = $this->createAdmin();
        $admin->assignRole('auditor');

        $this->assertTrue($admin->anyPermission(['edit_exports', 'view_reports'], 'web_admin'));
        $this->assertFalse($admin->anyPermission('edit_exports', 'web_admin'));
        $this->assertFalse($admin->anyPermission('view_reports', 'web'));
        $this->assertFalse($admin->anyPermission('does_not_exist', 'web_admin'));
    }

    #[Test]
    public function it_checks_the_user_signed_in_on_a_guard(): void
    {
        $admin = $this->createAdmin();
        $admin->assignRole('auditor');

        $this->assertFalse(Permissions::check('view_reports', 'web_admin'));

        $this->actingAs($admin, 'web_admin');

        $this->assertTrue(Permissions::check('view_reports', 'web_admin'));
        $this->assertTrue(Permissions::check(['edit_exports', 'view_exports'], 'web_admin'));
        $this->assertFalse(Permissions::check('edit_exports', 'web_admin'));
        $this->assertFalse(Permissions::check('view_reports', 'web'));
    }

    #[Test]
    public function the_anypermission_blade_directive_takes_a_guard(): void
    {
        $admin = $this->createAdmin();
        $admin->assignRole('auditor');

        $this->actingAs($admin, 'web_admin');

        $view = "@anypermission(['view_reports'], 'web_admin')ތަފްސީލު@endanypermission|@anypermission('view_reports', 'web')ނެތް@endanypermission";

        $this->assertEquals('ތަފްސީލު|', Blade::render($view));
    }

    #[Test]
    public function update_role_only_uses_roles_of_the_models_guard(): void
    {
        $admin = $this->createAdmin();
        Role::query()->create(['name' => 'web_only', 'guard_name' => 'web']);

        $causer = $this->createUser();
        Gate::define('updateRole', fn () => true);

        $admin->updateRole(['web_only'], causer: $causer);
        $this->assertEmpty($admin->roles()->pluck('name')->all());

        $admin->updateRole(['web_only', 'auditor'], causer: $causer);
        $this->assertEquals(['auditor'], $admin->roles()->pluck('name')->all());
        $this->assertEquals('web_admin', $admin->roles()->first()->guard_name);
    }

    protected function createUser(): User
    {
        return User::query()->create(['name' => 'އަޙްމަދު', 'email' => 'ahmed@example.com']);
    }

    protected function createAdmin(): Admin
    {
        return Admin::query()->create(['name' => 'ޢާއިޝަތު', 'email' => 'aishath@example.com']);
    }
}
