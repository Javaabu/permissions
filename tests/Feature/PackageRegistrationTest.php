<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\Fixtures\PackageServiceProvider;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\Models\Post;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\Policies\PostPolicy;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PackageRegistrationTest extends TestCase
{
    use InteractsWithDatabase;

    protected bool $flush_registry = false;

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            PackageServiceProvider::class,
        ];
    }

    #[Test]
    public function a_package_registers_permissions_and_its_policy_from_its_service_provider(): void
    {
        $this->assertTrue(Permissions::has('edit_posts'));
        $this->assertEquals('Posts', Permissions::label('posts'));
        $this->assertInstanceOf(PostPolicy::class, Gate::getPolicyFor(Post::class));
    }

    #[Test]
    public function the_package_permissions_sync_and_authorize_through_the_policy(): void
    {
        config()->set('permissions.roles', ['editor' => ['description' => 'Editor']]);

        $this->artisan('permissions:sync')->assertSuccessful();

        $user = User::query()->create([
            'name' => 'މަރިޔަމް',
            'email' => 'mariyam@example.com',
        ]);

        $this->assertFalse($user->can('create', Post::class));

        $user->assignRole('editor');

        $this->assertTrue($user->can('create', Post::class));
        $this->assertFalse($user->can('publish', Post::class));
    }
}
