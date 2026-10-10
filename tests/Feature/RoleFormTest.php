<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RoleFormTest extends TestCase
{
    use InteractsWithDatabase;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [FormsServiceProvider::class]);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();

        // Session is needed by forms package to retrieve old form valuues using old() helper
        $this->app['request']->setLaravelSession($this->app['session']->driver());
        View::share('errors', new ViewErrorBag());
    }

    #[Test]
    public function it_renders_a_role_form_with_permissions_and_an_cancel_redirect_link(): void
    {
        $permission = Permission::create([
            'name' => 'edit-posts',
            'description' => 'Edit posts',
            'model' => 'posts',
            'guard_name' => 'web',
        ]);

        $html = Blade::render(
            '<x-permissions::role-form :permissions="$permissions" cancel-redirect-url="/admin/roles" framework="material-admin-26" />',
            ['permissions' => Permission::all()]
        );

        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="permissions[]"', $html);
        $this->assertStringContainsString('value="'.$permission->id.'"', $html);
        $this->assertStringContainsString('Edit posts', $html);
        $this->assertStringContainsString('href="/admin/roles"', $html);
    }

    #[Test]
    public function it_uses_the_default_framework_and_cancel_redirect_url(): void
    {
        Route::get('/admin/roles', fn () => '')->name('admin.roles.index');

        $html = Blade::render(
            '<x-permissions::role-form :permissions="$permissions" />',
            ['permissions' => collect()]
        );

        $this->assertStringContainsString('#', $html);
        $this->assertStringContainsString('name="description"', $html);
    }

    #[Test]
    public function it_checks_permissions_assigned_to_the_role(): void
    {
        $permission = Permission::create([
            'name' => 'edit-posts',
            'description' => 'Edit posts',
            'model' => 'posts',
            'guard_name' => 'web',
        ]);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $html = Blade::render(
            '<x-permissions::role-form :permissions="$permissions" :role="$role" cancel-redirect-url="/admin/roles" framework="material-admin-26" />',
            ['permissions' => Permission::all(), 'role' => $role]
        );

        $this->assertMatchesRegularExpression('/<input[^>]*id="permission-'.$permission->id.'"[^>]*checked/', $html);
    }
}
