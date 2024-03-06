<?php

namespace Javaabu\Permissions\Tests\Feature;

use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Models\Role;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\TestCase;

class PermissionsTest extends TestCase
{
    use InteractsWithDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /** @test */
    public function it_can_check_if_a_user_has_a_permission(): void
    {
        $user = new User(['name' => 'Test User', 'email' => 'test@example.com']);
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

        $role->givePermissionTo('test');

        $user->assignRole('Test Role');

        $this->assertTrue($user->can('test'));
    }
}
