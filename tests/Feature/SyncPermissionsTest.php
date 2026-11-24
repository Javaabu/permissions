<?php

namespace Javaabu\Permissions\Tests\Feature;

use Javaabu\Permissions\Database\Seeders\PermissionsSeeder;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SyncPermissionsTest extends TestCase
{
    use InteractsWithDatabase;

    #[Test]
    public function it_creates_the_registered_permissions_with_their_group_and_description(): void
    {
        Permissions::register('exports', ['view_exports' => 'View exports']);

        $this->artisan('permissions:sync')
            ->expectsOutputToContain('Created permission web:view_exports')
            ->assertSuccessful();

        $this->assertDatabaseHas('permissions', [
            'name' => 'view_exports',
            'guard_name' => 'web',
            'model' => 'exports',
            'description' => 'View exports',
        ]);
    }

    #[Test]
    public function it_creates_permissions_for_every_configured_guard(): void
    {
        config()->set('permissions.guards', ['web', 'web_admin']);

        Permissions::register('exports', ['view_exports' => 'View exports']);

        Permissions::sync();

        $this->assertDatabaseHas('permissions', ['name' => 'view_exports', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_exports', 'guard_name' => 'web_admin']);
    }

    #[Test]
    public function it_only_syncs_the_given_guards(): void
    {
        config()->set('permissions.guards', ['web', 'web_admin']);

        Permissions::register('exports', ['view_exports' => 'View exports']);

        $this->artisan('permissions:sync', ['--guard' => ['web_admin']])->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'view_exports', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_exports', 'guard_name' => 'web_admin']);
    }

    #[Test]
    public function it_updates_changed_descriptions_and_is_idempotent(): void
    {
        Permissions::register('exports', ['view_exports' => 'View exports']);
        Permissions::sync();

        Permissions::register('exports', ['view_exports' => 'View all exports']);
        $result = Permissions::sync();

        $this->assertEquals(['web:view_exports'], $result->updated_permissions);
        $this->assertDatabaseHas('permissions', ['name' => 'view_exports', 'description' => 'View all exports']);

        $result = Permissions::sync();

        $this->assertEmpty($result->created_permissions);
        $this->assertEmpty($result->updated_permissions);
        $this->assertEquals(1, Permission::query()->where('name', 'view_exports')->count());
    }

    #[Test]
    public function it_prunes_permissions_removed_from_a_registered_group_only_when_asked(): void
    {
        Permission::query()->create(['name' => 'old_exports', 'guard_name' => 'web', 'model' => 'exports']);
        Permission::query()->create(['name' => 'manual_permission', 'guard_name' => 'web', 'model' => 'manual']);

        Permissions::register('exports', ['view_exports' => 'View exports']);

        $this->artisan('permissions:sync')->assertSuccessful();

        $this->assertDatabaseHas('permissions', ['name' => 'old_exports']);

        $this->artisan('permissions:sync', ['--prune' => true])
            ->expectsOutputToContain('Deleted permission web:old_exports')
            ->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'old_exports']);
        $this->assertDatabaseHas('permissions', ['name' => 'manual_permission']);
    }

    #[Test]
    public function it_creates_the_default_super_admin_role_with_every_permission(): void
    {
        Permission::query()->create(['name' => 'manual_permission', 'guard_name' => 'web']);
        Permissions::resource('exports');

        Permissions::sync();

        $role = Role::findByName('super_admin', 'web');

        $this->assertEquals('Super Admin', $role->description);
        $this->assertEqualsCanonicalizing(
            ['view_exports', 'edit_exports', 'delete_exports', 'force_delete_exports', 'manual_permission'],
            $role->permissions->pluck('name')->all(),
        );
    }

    #[Test]
    public function it_keeps_every_permission_roles_in_sync(): void
    {
        Permissions::register('exports', ['view_exports' => 'View exports']);
        Permissions::sync();

        Permissions::register('reports', ['view_reports' => 'View reports']);
        Permissions::sync(prune: true);

        $role = Role::findByName('super_admin', 'web');

        $this->assertEqualsCanonicalizing(['view_exports', 'view_reports'], $role->permissions->pluck('name')->all());
    }

    #[Test]
    public function it_gives_roles_the_permissions_matching_their_patterns_without_removing_others(): void
    {
        config()->set('permissions.roles', [
            'viewer' => ['description' => 'Viewer', 'permissions' => ['view_*']],
        ]);

        Permissions::resource('exports');
        Permissions::sync();

        $role = Role::findByName('viewer', 'web');
        $role->givePermissionTo('delete_exports');

        Permissions::sync();

        $this->assertEqualsCanonicalizing(['view_exports', 'delete_exports'], $role->fresh()->permissions->pluck('name')->all());
    }

    #[Test]
    public function it_creates_roles_defined_in_code_for_their_guards(): void
    {
        config()->set('permissions.roles', []);

        Permissions::register('exports', ['view_exports' => 'View exports'])->guards(['web', 'web_admin']);
        Permissions::role('auditor', 'Auditor', ['view_exports'], guards: 'web_admin');

        $this->artisan('permissions:sync')
            ->expectsOutputToContain('Created role web_admin:auditor')
            ->assertSuccessful();

        $this->assertDatabaseMissing('roles', ['name' => 'auditor', 'guard_name' => 'web']);
        $this->assertTrue(Role::findByName('auditor', 'web_admin')->hasPermissionTo('view_exports'));
    }

    #[Test]
    public function it_grants_group_permissions_to_existing_roles_and_skips_missing_ones(): void
    {
        config()->set('permissions.roles', [
            'editor' => ['description' => 'Editor', 'permissions' => []],
        ]);

        Permissions::resource('exports')
            ->grantTo('editor', only: ['view_exports', 'edit_exports', 'not_in_group'])
            ->grantTo('missing_role');

        $this->artisan('permissions:sync')
            ->expectsOutputToContain('Skipped grants to web:missing_role')
            ->assertSuccessful();

        $this->assertEqualsCanonicalizing(
            ['view_exports', 'edit_exports'],
            Role::findByName('editor', 'web')->permissions->pluck('name')->all(),
        );

        $this->assertDatabaseMissing('roles', ['name' => 'missing_role']);
    }

    #[Test]
    public function it_grants_every_permission_in_the_group_by_default(): void
    {
        config()->set('permissions.roles', ['editor' => []]);

        Permissions::resource('exports', actions: ['view', 'edit'])->grantTo(['editor']);

        Permissions::sync();

        $role = Role::findByName('editor', 'web');

        $this->assertEquals('Editor', $role->description);
        $this->assertEqualsCanonicalizing(['view_exports', 'edit_exports'], $role->permissions->pluck('name')->all());
    }

    #[Test]
    public function it_can_sync_from_a_seeder(): void
    {
        Permissions::register('exports', ['view_exports' => 'View exports']);

        $this->seed(PermissionsSeeder::class);

        $this->assertDatabaseHas('permissions', ['name' => 'view_exports']);
        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    #[Test]
    public function synced_permissions_are_usable_straight_away(): void
    {
        $user = $this->createUser();

        Permissions::register('exports', ['view_exports' => 'View exports']);
        Permissions::sync();

        $user->assignRole('super_admin');

        $this->assertTrue($user->can('view_exports'));
    }

    protected function createUser(): User
    {
        return User::query()->create([
            'name' => 'އަޙްމަދު',
            'email' => 'ahmed@example.com',
        ]);
    }
}
