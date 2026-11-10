<?php

namespace Javaabu\Permissions\Tests\Fixtures;

class DenyAllPolicy
{
    public function before(): bool
    {
        return false;
    }
}
