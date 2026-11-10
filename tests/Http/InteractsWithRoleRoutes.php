<?php

namespace Javaabu\Permissions\Tests\Http;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Javaabu\Manifest\Facades\Manifest;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Tests\Models\Admin;

/**
 * Role routes for the web_admin guard under /admin, with a posts group registered
 */
trait InteractsWithRoleRoutes
{
    protected function defineRoutes($router): void
    {
        Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
            Route::roles(['guard' => 'web_admin']);
        });
    }

    protected function setUpRoleRoutes(): void
    {
        View::addLocation(__DIR__ . '/../views');
        Manifest::fake();

        Permissions::resource('posts', label: 'Posts');
        Permissions::sync();
    }

    protected function admin(array $permissions = []): Admin
    {
        $admin = Admin::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'އެޑްމިން'],
        );

        if ($permissions) {
            $admin->givePermissionTo($permissions);
        } else {
            $admin->assignRole('super_admin');
        }

        return $admin;
    }
}
