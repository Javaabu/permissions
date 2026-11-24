<?php

namespace Javaabu\Permissions\Tests\Fixtures;

use Illuminate\Support\ServiceProvider;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\Models\Post;
use Javaabu\Permissions\Tests\Policies\PostPolicy;

/**
 * Registers permissions and a policy the way a package would
 */
class PackageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Permissions::resource('posts', label: 'Posts')
            ->grantTo('editor');

        Permissions::policy(Post::class, PostPolicy::class);
    }
}
