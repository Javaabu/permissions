<?php

namespace Javaabu\Permissions\Tests\Fixtures;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Javaabu\Permissions\Tests\Models\Post;
use Javaabu\Permissions\Tests\Policies\AppPostPolicy;

/**
 * Overrides a package policy the way an app would, booting after the package
 */
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Post::class, AppPostPolicy::class);
    }
}
