<?php

namespace Javaabu\Permissions\Tests\Http;

use Javaabu\Manifest\Facades\Manifest;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BladePagesTest extends TestCase
{
    use InteractsWithDatabase;
    use InteractsWithRoleRoutes;

    protected bool $flush_registry = false;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permissions.guards', ['web', 'web_admin']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRoleRoutes();
    }

    #[Test]
    public function the_index_lists_the_roles_with_their_permission_counts(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން', 'guard_name' => 'web_admin']);
        $role->givePermissionTo(['view_posts', 'edit_posts']);

        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('<h1 class="text-2xl font-semibold">Roles</h1>', false)
            ->assertSee('New role')
            ->assertSee(url('/admin/roles/create'), false)
            ->assertSee('ލިޔުންތައް ބަދަލުކުރާ މީހުން')
            ->assertSee('2 permissions')
            ->assertSee(url("/admin/roles/{$role->id}"), false)
            ->assertSee(url("/admin/roles/{$role->id}/edit"), false)
            ->assertSee('data-confirm="Delete this role? Users with it lose its permissions."', false)
            ->assertSee('Search roles');

        Manifest::assertAssetRequired('permissions::roles');
    }

    #[Test]
    public function the_index_shows_only_what_the_user_may_do(): void
    {
        Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);

        $this->actingAs($this->admin(['view_roles']), 'web_admin')
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('editor')
            ->assertDontSee('New role')
            ->assertDontSee('/edit', false)
            ->assertDontSee('data-confirm', false);
    }

    #[Test]
    public function a_search_without_results_says_so(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles?search=' . urlencode('ނެތް'))
            ->assertOk()
            ->assertSee("Roles matching 'ނެތް'")
            ->assertSee('No roles match your search.');
    }

    #[Test]
    public function the_create_page_has_the_fields_and_the_permission_groups(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles/create')
            ->assertOk()
            ->assertSee('New role')
            ->assertSee('action="' . url('/admin/roles') . '"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="description"', false)
            ->assertSee('data-permission-group="roles"', false)
            ->assertSee('data-permission-group="posts"', false)
            ->assertSee('data-permission-group-select="all"', false)
            ->assertSee('Edit posts')
            ->assertSee('Create role');

        Manifest::assertAssetRequired('permissions::roles');
    }

    #[Test]
    public function the_edit_page_ticks_the_granted_permissions(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('edit_posts');

        $granted = Permission::findByName('edit_posts', 'web_admin');
        $other = Permission::findByName('delete_posts', 'web_admin');

        $content = $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertOk()
            ->assertSee('value="editor"', false)
            ->assertSee('value="Editor"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Save')
            ->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*id="permission-' . $granted->id . '"[^>]*checked/', $content);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*id="permission-' . $other->id . '"[^>]*checked/', $content);
    }

    #[Test]
    public function a_failed_update_keeps_the_ticked_permissions(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $permission = Permission::findByName('delete_posts', 'web_admin');

        $this->actingAs($this->admin(), 'web_admin')
            ->from("/admin/roles/{$role->id}/edit")
            ->put("/admin/roles/{$role->id}", ['name' => 'ނަން', 'description' => 'Editor', 'permissions' => [$permission->id]])
            ->assertRedirect("/admin/roles/{$role->id}/edit")
            ->assertSessionHasErrors('name');

        $content = $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertSee('value="ނަން"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*id="permission-' . $permission->id . '"[^>]*checked/', $content);
        $this->assertEmpty($role->fresh()->permissions);
    }

    #[Test]
    public function the_show_page_lists_the_granted_permissions(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);
        $role->givePermissionTo('edit_posts');

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}")
            ->assertOk()
            ->assertSee('Edit posts')
            ->assertDontSee('Delete posts')
            ->assertSee('web_admin')
            ->assertSee('Back to roles');

        $empty = Role::query()->create(['name' => 'viewer', 'description' => 'Viewer', 'guard_name' => 'web_admin']);

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$empty->id}")
            ->assertSee('This role has no permissions.');
    }

    #[Test]
    public function the_pages_are_in_dhivehi_and_right_to_left(): void
    {
        __('Roles', locale: 'dv');
        $this->app['translator']->addLines(['*.Posts' => 'ޕޯސްޓްތައް', '*.Edit posts' => 'ޕޯސްޓްތައް ބަދަލުކުރުން'], 'dv');
        $this->app->setLocale('dv');

        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);

        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles')
            ->assertSee('dir="rtl"', false)
            ->assertSee('ރޯލްތައް')
            ->assertSee('އައު ރޯލެއް')
            ->assertSee('ރޯލްތައް ހޯދާ');

        $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertSee('ޕޯސްޓްތައް')
            ->assertSee('ޕޯސްޓްތައް ބަދަލުކުރުން')
            ->assertSee('ހުރިހާ އެއްޗެއް ޚިޔާރުކުރޭ')
            ->assertSee('ސޭވްކުރޭ');
    }

    #[Test]
    public function the_pages_use_no_physical_direction_classes(): void
    {
        foreach (glob(__DIR__ . '/../../resources/views/roles/{,partials/}*.blade.php', GLOB_BRACE) as $view) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b(ml|mr|pl|pr|left|right|text-left|text-right|border-l|border-r|rounded-l|rounded-r)-/',
                file_get_contents($view),
                basename($view) . ' should use logical utilities (ms-, pe-, start-, text-start).',
            );
        }
    }

    #[Test]
    public function the_pages_render_with_bootstrap_too(): void
    {
        config()->set('forms.framework', 'bootstrap-6');

        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editor', 'guard_name' => 'web_admin']);

        $this->actingAs($this->admin(), 'web_admin')->get('/admin/roles')->assertOk()->assertSee('editor');
        $this->actingAs($this->admin(), 'web_admin')->get("/admin/roles/{$role->id}/edit")->assertOk()->assertSee('Edit posts');
        $this->actingAs($this->admin(), 'web_admin')->get("/admin/roles/{$role->id}")->assertOk();
    }
}
