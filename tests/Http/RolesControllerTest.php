<?php

namespace Javaabu\Permissions\Tests\Http;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Inertia\ServiceProvider;
use Inertia\Testing\AssertableInertia;
use Javaabu\Permissions\Events\RolePermissionsUpdated;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\Fixtures\CustomRolesController;
use Javaabu\Permissions\Tests\Fixtures\DenyAllPolicy;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\Models\Admin;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RolesControllerTest extends TestCase
{
    use InteractsWithDatabase;

    protected bool $flush_registry = false;

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permissions.guards', ['web', 'web_admin']);
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
            Route::roles(['guard' => 'web_admin']);
        });

        Route::middleware('web')->group(function () {
            Route::roles(['guard' => 'web', 'prefix' => 'account/roles', 'name' => 'account.roles']);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        View::prependNamespace('permissions', __DIR__ . '/../views');
        View::addLocation(__DIR__ . '/../views');

        Permissions::resource('posts', label: 'Posts');
        Permissions::sync();
    }

    #[Test]
    public function the_macro_registers_named_routes_under_the_apps_prefix(): void
    {
        $this->assertEquals('http://localhost/admin/roles/5/edit', route('admin.roles.edit', 5));
        $this->assertEquals('http://localhost/account/roles', route('account.roles.index'));
        $this->assertEquals(
            ['web', 'auth:web_admin'],
            Route::getRoutes()->getByName('admin.roles.index')->gatherMiddleware(),
        );
    }

    #[Test]
    public function it_lists_only_the_roles_of_the_routes_guard_as_json(): void
    {
        Role::query()->create(['name' => 'editor', 'description' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން', 'guard_name' => 'web_admin']);
        Role::query()->create(['name' => 'member', 'guard_name' => 'web']);

        $this->actingAs($this->admin(), 'web_admin')
            ->getJson('/admin/roles')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'editor')
            ->assertJsonPath('data.0.description', 'ލިޔުންތައް ބަދަލުކުރާ މީހުން')
            ->assertJsonPath('data.1.name', 'super_admin')
            ->assertJsonPath('meta.total', 2);
    }

    #[Test]
    public function it_searches_roles(): void
    {
        Role::query()->create(['name' => 'editor', 'description' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން', 'guard_name' => 'web_admin']);

        $this->actingAs($this->admin(), 'web_admin')
            ->getJson('/admin/roles?search=' . urlencode('ލިޔުން'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'editor');
    }

    #[Test]
    public function it_renders_the_blade_index(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles')
            ->assertOk()
            ->assertViewIs('permissions::roles.index')
            ->assertSee('<h1>Roles</h1>', false)
            ->assertSee('web_admin|admin.roles.index')
            ->assertSee('<li>Super Admin</li>', false);
    }

    #[Test]
    public function it_renders_inertia_pages_when_configured(): void
    {
        config()->set('permissions.views', 'inertia');

        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Index')
                ->where('guard', 'web_admin')
                ->where('routes.edit', 'admin.roles.edit')
                ->has('roles.data', 1)
                ->where('roles.data.0.name', 'super_admin'));

        $role = Role::findByName('super_admin', 'web_admin');

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Edit')
                ->where('role.name', 'super_admin')
                ->where('permission_groups.0.name', 'roles')
                ->where('permission_groups.0.permissions.0.name', 'view_roles')
                ->where('permission_groups.0.permissions.0.granted', true)
                ->where('permission_groups.1.label', 'Posts'));
    }

    #[Test]
    public function it_shows_the_form_data_for_a_new_role(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->getJson('/admin/roles/create')
            ->assertOk()
            ->assertJsonPath('data.role', null)
            ->assertJsonPath('data.guard', 'web_admin')
            ->assertJsonPath('data.permission_groups.0.name', 'roles')
            ->assertJsonPath('data.permission_groups.0.label', 'Roles')
            ->assertJsonPath('data.permission_groups.0.permissions.0.granted', false)
            ->assertJsonPath('data.permission_groups.1.name', 'posts');
    }

    #[Test]
    public function the_edit_page_marks_granted_permissions_in_dhivehi(): void
    {
        __('Roles', locale: 'dv');
        $this->app['translator']->addLines(['*.Edit posts' => 'ޕޯސްޓްތައް ބަދަލުކުރުން'], 'dv');
        $this->app->setLocale('dv');

        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('edit_posts');

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertOk()
            ->assertSee('<span class="granted">ޕޯސްޓްތައް ބަދަލުކުރުން</span>', false)
            ->assertSee('<h2>ރޯލްތައް</h2>', false);
    }

    #[Test]
    public function it_creates_a_role_for_the_routes_guard_with_permissions(): void
    {
        $admin = $this->admin();
        $permission = Permission::findByName('edit_posts', 'web_admin');

        $this->actingAs($admin, 'web_admin')
            ->postJson('/admin/roles', [
                'name' => 'editor',
                'description' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން',
                'permissions' => [$permission->id],
            ])
            ->assertCreated()
            ->assertJsonPath('data.guard_name', 'web_admin')
            ->assertJsonPath('data.permissions.0.name', 'edit_posts');

        $role = Role::findByName('editor', 'web_admin');

        $this->assertDatabaseHas('roles', ['name' => 'editor', 'guard_name' => 'web_admin', 'description' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން']);
        $this->assertDatabaseMissing('roles', ['name' => 'editor', 'guard_name' => 'web']);
        $this->assertTrue($role->hasPermissionTo('edit_posts'));

        $this->assertDatabaseHas('activity_log', [
            'description' => 'permissions_updated',
            'subject_id' => $role->id,
            'causer_type' => $admin->getMorphClass(),
            'causer_id' => $admin->id,
        ]);
    }

    #[Test]
    public function storing_from_a_form_redirects_to_the_edit_page_with_a_message(): void
    {
        $response = $this->actingAs($this->admin(), 'web_admin')
            ->post('/admin/roles', ['name' => 'editor', 'description' => 'Editor']);

        $role = Role::findByName('editor', 'web_admin');

        $response->assertRedirect(route('admin.roles.edit', $role))
            ->assertSessionHas('success', 'Role created');
    }

    #[Test]
    public function it_validates_roles(): void
    {
        Role::query()->create(['name' => 'editor', 'guard_name' => 'web_admin']);
        Role::query()->create(['name' => 'member', 'guard_name' => 'web']);

        $web_permission = Permission::findByName('edit_posts', 'web');

        $this->actingAs($this->admin(), 'web_admin')
            ->postJson('/admin/roles', [
                'name' => 'editor',
                'permissions' => [$web_permission->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'permissions.0']);

        $this->actingAs($this->admin(), 'web_admin')
            ->postJson('/admin/roles', ['name' => 'ނަން', 'description' => 'Editor'])
            ->assertJsonValidationErrors(['name']);

        $this->actingAs($this->admin(), 'web_admin')
            ->postJson('/admin/roles', ['name' => 'member', 'description' => 'Member'])
            ->assertCreated();
    }

    #[Test]
    public function it_updates_a_role_and_its_permissions(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('view_posts');

        $permission = Permission::findByName('edit_posts', 'web_admin');

        $this->actingAs($this->admin(), 'web_admin')
            ->putJson("/admin/roles/{$role->id}", [
                'name' => 'editor',
                'description' => 'Content editor',
                'permissions' => [$permission->id],
            ])
            ->assertOk()
            ->assertJsonPath('data.description', 'Content editor');

        $role->refresh();

        $this->assertEquals(['edit_posts'], $role->permissions->pluck('name')->all());
    }

    #[Test]
    public function a_json_update_without_permissions_keeps_them(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('view_posts');

        $this->actingAs($this->admin(), 'web_admin')
            ->patchJson("/admin/roles/{$role->id}", ['description' => 'ބަދަލުކުރާ މީހުން'])
            ->assertOk()
            ->assertJsonPath('data.name', 'editor');

        $this->assertEquals(['view_posts'], $role->fresh()->permissions->pluck('name')->all());
    }

    #[Test]
    public function a_form_update_with_no_permissions_ticked_removes_them(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('view_posts');

        $this->actingAs($this->admin(), 'web_admin')
            ->put("/admin/roles/{$role->id}", ['name' => 'editor', 'description' => 'Editor'])
            ->assertRedirect(route('admin.roles.edit', $role))
            ->assertSessionHas('success', 'Role updated');

        $this->assertEmpty($role->fresh()->permissions);
    }

    #[Test]
    public function it_shows_a_role(): void
    {
        $role = Role::findByName('super_admin', 'web_admin');

        $this->actingAs($this->admin(), 'web_admin')
            ->getJson("/admin/roles/{$role->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'super_admin');

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}")
            ->assertOk()
            ->assertViewIs('permissions::roles.show')
            ->assertViewHas('permission_groups');
    }

    #[Test]
    public function it_deletes_a_role(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'guard_name' => 'web_admin']);
        $other = Role::query()->create(['name' => 'author', 'guard_name' => 'web_admin']);

        $this->actingAs($this->admin(), 'web_admin')
            ->deleteJson("/admin/roles/{$role->id}")
            ->assertNoContent();

        $this->actingAs($this->admin(), 'web_admin')
            ->delete("/admin/roles/{$other->id}")
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success', 'Role deleted');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseMissing('roles', ['id' => $other->id]);
    }

    #[Test]
    public function roles_of_another_guard_are_not_found(): void
    {
        $role = Role::query()->create(['name' => 'member', 'guard_name' => 'web']);

        $this->actingAs($this->admin(), 'web_admin')
            ->getJson("/admin/roles/{$role->id}")
            ->assertNotFound();

        $this->actingAs($this->admin(), 'web_admin')
            ->deleteJson("/admin/roles/{$role->id}")
            ->assertNotFound();
    }

    #[Test]
    public function it_needs_the_role_permissions(): void
    {
        $admin = Admin::query()->create(['name' => 'ޢަލީ', 'email' => 'ali@example.com']);
        $role = Role::findByName('super_admin', 'web_admin');

        $this->actingAs($admin, 'web_admin')->getJson('/admin/roles')->assertForbidden();
        $this->actingAs($admin, 'web_admin')->getJson('/admin/roles/create')->assertForbidden();
        $this->actingAs($admin, 'web_admin')->postJson('/admin/roles', ['name' => 'x', 'description' => 'x'])->assertForbidden();
        $this->actingAs($admin, 'web_admin')->getJson("/admin/roles/{$role->id}")->assertForbidden();
        $this->actingAs($admin, 'web_admin')->getJson("/admin/roles/{$role->id}/edit")->assertForbidden();
        $this->actingAs($admin, 'web_admin')->putJson("/admin/roles/{$role->id}", [])->assertForbidden();
        $this->actingAs($admin, 'web_admin')->deleteJson("/admin/roles/{$role->id}")->assertForbidden();

        $admin->givePermissionTo('view_roles');

        $this->actingAs($admin, 'web_admin')->getJson('/admin/roles')->assertOk();
        $this->actingAs($admin, 'web_admin')->getJson("/admin/roles/{$role->id}")->assertOk();
        $this->actingAs($admin, 'web_admin')->getJson("/admin/roles/{$role->id}/edit")->assertForbidden();
    }

    #[Test]
    public function each_guard_manages_its_own_roles(): void
    {
        $user = User::query()->create(['name' => 'މަރިޔަމް', 'email' => 'mariyam@example.com']);

        $this->getJson('/admin/roles')->assertUnauthorized();
        $this->actingAs($user, 'web')->getJson('/admin/roles')->assertUnauthorized();
        $this->actingAs($user, 'web')->getJson('/account/roles')->assertForbidden();

        $user->assignRole('super_admin');

        $this->actingAs($user, 'web')
            ->getJson('/account/roles')
            ->assertOk()
            ->assertJsonPath('data.0.guard_name', 'web');

        $this->actingAs($user, 'web')
            ->postJson('/account/roles', ['name' => 'member', 'description' => 'Member'])
            ->assertCreated()
            ->assertJsonPath('data.guard_name', 'web');
    }

    #[Test]
    public function the_controller_can_be_overridden_from_config(): void
    {
        config()->set('permissions.controllers.roles', CustomRolesController::class);

        Route::roles(['guard' => 'web_admin', 'prefix' => 'custom', 'name' => 'custom']);

        $this->actingAs($this->admin(), 'web_admin')
            ->getJson('/custom')
            ->assertOk()
            ->assertJson(['custom' => true]);
    }

    #[Test]
    public function the_controller_can_be_overridden_per_route(): void
    {
        Route::roles(['guard' => 'web_admin', 'prefix' => 'other', 'name' => 'other', 'controller' => CustomRolesController::class, 'middleware' => []]);

        $this->getJson('/other')->assertOk()->assertJson(['custom' => true]);
    }

    #[Test]
    public function the_role_policy_can_be_overridden_from_config(): void
    {
        config()->set('permissions.policies', [Role::class => DenyAllPolicy::class]);
        Permissions::applyPolicies();

        $this->actingAs($this->admin(), 'web_admin')
            ->getJson('/admin/roles')
            ->assertForbidden();
    }

    #[Test]
    public function the_role_events_fire_with_the_admin_as_causer(): void
    {
        Event::fake([RolePermissionsUpdated::class]);

        $admin = $this->admin();
        $permission = Permission::findByName('view_posts', 'web_admin');

        $this->actingAs($admin, 'web_admin')
            ->postJson('/admin/roles', ['name' => 'viewer', 'description' => 'Viewer', 'permissions' => [$permission->id]])
            ->assertCreated();

        Event::assertDispatched(
            RolePermissionsUpdated::class,
            fn (RolePermissionsUpdated $event) => $event->causer->is($admin) && $event->new_permissions === ['view_posts'],
        );
    }

    protected function admin(): Admin
    {
        $admin = Admin::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'އެޑްމިން'],
        );

        $admin->assignRole('super_admin');

        return $admin;
    }
}
