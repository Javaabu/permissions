<?php

namespace Javaabu\Permissions\Tests\Feature;

use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PermissionRegistryTest extends TestCase
{
    #[Test]
    public function it_registers_permissions_under_a_group(): void
    {
        Permissions::register('exports', [
            'view_exports' => 'View exports',
            'download_exports' => 'Download exports',
        ], label: 'Exports');

        $this->assertTrue(Permissions::has('view_exports'));
        $this->assertEquals('Download exports', Permissions::description('download_exports'));
        $this->assertEquals('exports', Permissions::groupOf('view_exports')->name);
        $this->assertEquals('Exports', Permissions::label('exports'));
        $this->assertEquals(['view_exports', 'download_exports'], array_keys(Permissions::permissions()));
    }

    #[Test]
    public function it_adds_to_a_group_registered_twice(): void
    {
        Permissions::register('exports', ['view_exports' => 'View exports']);
        Permissions::register('exports', ['delete_exports' => 'Delete exports']);

        $this->assertCount(1, Permissions::groups());
        $this->assertEquals(['view_exports', 'delete_exports'], array_keys(Permissions::group('exports')->getPermissions()));
    }

    #[Test]
    public function it_makes_descriptions_from_names_given_as_a_list(): void
    {
        Permissions::register('reports', ['view_reports', 'download_reports']);

        $this->assertEquals('View reports', Permissions::description('view_reports'));
        $this->assertEquals('Reports', Permissions::label('reports'));
    }

    #[Test]
    public function it_registers_the_standard_permissions_of_a_resource(): void
    {
        Permissions::resource('oauth_clients', 'OAuth2 clients', ['view', 'edit', 'edit_others', 'force_delete', 'archive']);

        $this->assertEquals([
            'view_oauth_clients' => 'View OAuth2 clients',
            'edit_oauth_clients' => 'Edit OAuth2 clients',
            'edit_others_oauth_clients' => 'Edit all OAuth2 clients',
            'force_delete_oauth_clients' => 'Permanently delete OAuth2 clients',
            'archive_oauth_clients' => 'Archive OAuth2 clients',
        ], Permissions::permissions());

        $this->assertEquals('Oauth Clients', Permissions::label('oauth_clients'));
    }

    #[Test]
    public function it_uses_the_default_actions_and_group_name_for_a_resource(): void
    {
        Permissions::resource('blog_posts');

        $this->assertEquals([
            'view_blog_posts' => 'View blog posts',
            'edit_blog_posts' => 'Edit blog posts',
            'delete_blog_posts' => 'Delete blog posts',
            'force_delete_blog_posts' => 'Permanently delete blog posts',
        ], Permissions::permissions());
    }

    #[Test]
    public function it_uses_the_default_auth_guard_when_no_guards_are_configured(): void
    {
        $this->assertEquals(['web'], Permissions::defaultGuards());
    }

    #[Test]
    public function it_uses_the_configured_guards(): void
    {
        config()->set('permissions.guards', ['web_admin', 'api']);

        Permissions::register('exports', ['view_exports']);
        Permissions::register('reports', ['view_reports'])->guards('web');

        $this->assertEquals(['web_admin', 'api'], Permissions::guardsFor(Permissions::group('exports')));
        $this->assertEquals(['web'], Permissions::guardsFor(Permissions::group('reports')));
        $this->assertEquals(['view_reports'], array_keys(Permissions::permissions('web')));
        $this->assertEquals(['view_exports'], array_keys(Permissions::permissions('api')));
        $this->assertEquals(['web_admin', 'api', 'web'], Permissions::allGuards());
    }

    #[Test]
    public function it_merges_configured_roles_over_roles_defined_in_code(): void
    {
        config()->set('permissions.roles', [
            'editor' => ['description' => 'Editor', 'permissions' => ['edit_*']],
        ]);

        Permissions::role('editor', 'Content editor', ['view_*']);
        Permissions::role('viewer', permissions: ['view_*'], guards: 'web_admin');

        $roles = Permissions::roles();

        $this->assertEquals(['editor', 'viewer'], array_keys($roles));
        $this->assertEquals('Editor', $roles['editor']->getDescription());
        $this->assertEquals(['edit_*'], $roles['editor']->permissions);
        $this->assertEquals('Viewer', $roles['viewer']->getDescription());
        $this->assertEquals(['web_admin'], $roles['viewer']->guards);
    }

    #[Test]
    public function it_builds_conventional_permission_names(): void
    {
        $this->assertEquals('force_delete_exports', Permissions::permissionName('force_delete', 'exports'));
    }

    #[Test]
    public function it_returns_unknown_lookups_as_null(): void
    {
        $this->assertFalse(Permissions::has('nothing'));
        $this->assertNull(Permissions::description('nothing'));
        $this->assertNull(Permissions::groupOf('nothing'));
        $this->assertNull(Permissions::group('nothing'));
        $this->assertEquals('Blog Posts', Permissions::label('blog_posts'));
    }
}
