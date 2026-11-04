<?php

namespace Javaabu\Permissions\Tests\Http;

use Inertia\ServiceProvider as InertiaServiceProvider;
use Inertia\Testing\AssertableInertia;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InertiaPagesTest extends TestCase
{
    use InteractsWithDatabase;
    use InteractsWithRoleRoutes;

    protected bool $flush_registry = false;

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), InertiaServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permissions.guards', ['web', 'web_admin']);
        $app['config']->set('permissions.views', 'inertia');
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRoleRoutes();
    }

    #[Test]
    public function the_index_gets_the_roles_links_abilities_and_dhivehi_strings(): void
    {
        __('Roles', locale: 'dv');
        app()->setLocale('dv');

        $role = Role::query()->create(['name' => 'editor', 'description' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('edit_posts');

        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Index')
                ->where('title', 'ރޯލްތައް')
                ->where('guard', 'web_admin')
                ->where('roles.data.0.name', 'editor')
                ->where('roles.data.0.permissions_count', 1)
                ->where('roles.data.0.can.update', true)
                ->where('roles.data.0.links.edit', url("/admin/roles/{$role->id}/edit"))
                ->where('urls.create', url('/admin/roles/create'))
                ->where('can.create', true)
                ->where('strings.new_role', 'އައު ރޯލެއް')
                ->where('strings.permissions_count', ':count ހުއްދަ')
                ->where('search', null)
                ->has('roles.meta.last_page'));
    }

    #[Test]
    public function the_create_page_gets_the_permission_groups(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles/create')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Create')
                ->where('role', null)
                ->where('translations', null)
                ->where('urls.store', url('/admin/roles'))
                ->where('permission_groups.1.label', 'Posts')
                ->where('permission_groups.1.permissions.1.description', 'Edit posts')
                ->where('permission_groups.1.permissions.1.granted', false));
    }

    #[Test]
    public function the_edit_and_show_pages_get_the_role(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('view_posts');

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Edit')
                ->where('role.name', 'editor')
                ->where('urls.update', url("/admin/roles/{$role->id}"))
                ->where('can.delete', true)
                ->where('permission_groups.1.permissions.0.granted', true));

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Show')
                ->where('role.permissions.0.name', 'view_posts')
                ->has('permission_groups', 1)
                ->where('strings.no_permissions', 'This role has no permissions.'));
    }

    #[Test]
    public function json_requests_still_get_json(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->getJson('/admin/roles')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'super_admin')
            ->assertJsonMissingPath('strings');
    }
}
