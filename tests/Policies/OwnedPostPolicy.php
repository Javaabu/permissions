<?php

namespace Javaabu\Permissions\Tests\Policies;

use Javaabu\Permissions\Policies\ResourcePolicy;

class OwnedPostPolicy extends ResourcePolicy
{
    protected string $resource = 'posts';

    protected bool $owned = true;
}
