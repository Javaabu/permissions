<?php

namespace Javaabu\Permissions\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait InteractsWithDatabase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database');
    }
}
