<?php

namespace Javaabu\Permissions\Tests\Policies;

use Javaabu\Permissions\Policies\ResourcePolicy;

class AdminPostPolicy extends ResourcePolicy
{
    protected string $resource = 'posts';

    protected ?string $guard = 'web_admin';
}
