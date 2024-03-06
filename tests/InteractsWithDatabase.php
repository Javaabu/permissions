<?php

namespace Javaabu\Permissions\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait InteractsWithDatabase
{
    use RefreshDatabase;

    protected function runMigrations()
    {
        include_once __DIR__ . '/database/create_users_table.php';

        (new \CreateUsersTable)->up();
    }
}
