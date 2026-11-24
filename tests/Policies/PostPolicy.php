<?php

namespace Javaabu\Permissions\Tests\Policies;

use Javaabu\Permissions\Policies\ResourcePolicy;

class PostPolicy extends ResourcePolicy
{
    protected string $resource = 'posts';

    protected array $abilities = [
        'publish' => 'publish_posts',
        'review' => ['approve_posts', 'publish_posts'],
    ];
}
