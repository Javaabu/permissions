<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Javaabu\Permissions\Tests\Fixtures\AppServiceProvider;
use Javaabu\Permissions\Tests\Fixtures\PackageServiceProvider;
use Javaabu\Permissions\Tests\Models\Post;
use Javaabu\Permissions\Tests\Policies\AppPostPolicy;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AppPolicyOverrideTest extends TestCase
{
    protected bool $flush_registry = false;

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            PackageServiceProvider::class,
            AppServiceProvider::class,
        ];
    }

    #[Test]
    public function an_app_gate_policy_defined_while_booting_overrides_the_package_policy(): void
    {
        $this->assertInstanceOf(AppPostPolicy::class, Gate::getPolicyFor(Post::class));
    }
}
