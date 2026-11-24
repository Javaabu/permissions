<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Registry\PermissionRegistry;
use Javaabu\Permissions\Tests\Models\Post;
use Javaabu\Permissions\Tests\Policies\AppPostPolicy;
use Javaabu\Permissions\Tests\Policies\PostPolicy;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PolicyRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_a_package_policy_on_the_gate(): void
    {
        Permissions::policy(Post::class, PostPolicy::class);

        $this->assertInstanceOf(PostPolicy::class, Gate::getPolicyFor(Post::class));
        $this->assertEquals([Post::class => PostPolicy::class], Permissions::policies());
    }

    #[Test]
    public function the_config_overrides_a_package_policy(): void
    {
        config()->set('permissions.policies', [Post::class => AppPostPolicy::class]);

        Permissions::policy(Post::class, PostPolicy::class);

        $this->assertInstanceOf(AppPostPolicy::class, Gate::getPolicyFor(Post::class));
        $this->assertEquals([Post::class => AppPostPolicy::class], Permissions::policies());
    }

    #[Test]
    public function the_config_overrides_a_policy_already_on_the_gate(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        config()->set('permissions.policies', [Post::class => AppPostPolicy::class]);

        Permissions::applyPolicies();

        $this->assertInstanceOf(AppPostPolicy::class, Gate::getPolicyFor(Post::class));
    }

    #[Test]
    public function a_policy_the_app_defined_on_the_gate_wins_over_a_package_policy(): void
    {
        Gate::policy(Post::class, AppPostPolicy::class);

        Permissions::policy(Post::class, PostPolicy::class);

        $this->assertInstanceOf(AppPostPolicy::class, Gate::getPolicyFor(Post::class));
    }

    #[Test]
    public function package_policies_wait_for_the_app_to_boot_so_the_app_can_override_them(): void
    {
        $registry = new PermissionRegistry($this->app);

        $registry->policy(Post::class, PostPolicy::class);

        $this->assertArrayNotHasKey(Post::class, Gate::policies());

        Gate::policy(Post::class, AppPostPolicy::class);

        $registry->applyPolicies();

        $this->assertInstanceOf(AppPostPolicy::class, Gate::getPolicyFor(Post::class));
    }
}
