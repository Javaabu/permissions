---
title: Upgrading from v2
sidebar_position: 1.25
---

v3 adds the registration API, multi-auth helpers and role management, and drops two dependencies. Most apps need these steps.

# Requirements

PHP 8.4 and Laravel 12 or 13.

```bash
composer require javaabu/permissions:^3.0
```

# New dependencies and a migration

v3 requires javaabu/forms v2 (the Blade pages) and javaabu/manifest (the page assets). Until they're on Packagist, add their repositories as shown in [Requirements](requirements.md).

A new migration, `add_translations_to_roles_table`, adds two nullable columns (`translations` and `lang`) to the roles table, for [translatable role descriptions](advanced-usage/translatable-roles.md). It runs with your other migrations and is harmless without translatable. If you publish the package's migrations, publish again to get it.

# javaabu/helpers and javaabu/activitylog are no longer required

v2 required both. v3 doesn't, so that apps can move to the next versions of those packages independently. If you use them for anything else, require them yourself. (javaabu/forms v2, which v3 requires, still installs javaabu/helpers for now; that's tracked in [Javaabu/forms#18](https://github.com/Javaabu/forms/issues/18).)

- **Activity logging** of `role_updated` and `permissions_updated` now uses spatie/laravel-activitylog directly, when it's installed. javaabu/activitylog installs it, so if you still have it, logging keeps working. See [Activity log](advanced-usage/activity-log.md).
- **`Role` no longer uses `LogsActivity`**, so changes to a role's name and description aren't logged. To keep logging them, [extend the model](advanced-usage/activity-log.md#logging-changes-to-the-role-itself).
- **`Role` no longer implements `AdminModel`.** `admin_url`, `admin_link` and `admin_link_name` are gone. `search()` still works. If you need `AdminModel`, extend the model:

```php
namespace App\Models;

use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Permissions\Models\Role as BaseRole;

class Role extends BaseRole implements AdminModel
{
    use IsAdminModel;

    public function getAdminUrlAttribute(): string
    {
        return route('admin.roles.edit', $this);
    }

    public function getAdminLinkNameAttribute(): string
    {
        return $this->description;
    }
}
```

and set it as `permission.models.role` in `config/permission.php`.

# Factories and seeders moved

| v2 | v3 |
| --- | --- |
| `Database\Factories\RoleFactory` | `Javaabu\Permissions\Database\Factories\RoleFactory` |
| `Database\Factories\PermissionFactory` | `Javaabu\Permissions\Database\Factories\PermissionFactory` |

v2 put them in the app's own `Database\Factories` namespace, which could clash with yours. `Role::factory()` and `Permission::factory()` work as before.

# `HasRoles`

- `updateRole()` no longer reads `get_setting('default_role')`. Set `default_role` in `config/permissions.php`, or pass it: `$user->updateRole($role, new: true, default_role: get_setting('default_role'))`. It also takes the causer: `causer: $request->user()`.
- `updateRole()` only uses roles of the user's guard.
- The `permission()` scope is now spatie's own, which also counts direct permissions and matches any of the permissions. For v2's behaviour (roles only, all of them), use `whereRoleHasPermissions()`.
- `anyPermission()` takes an optional guard.

# Events

- `RolePermissionsUpdated::$role` is typed `Model`, and `$causer` is `?Model` instead of `Illuminate\Foundation\Auth\User`.
- `UserRoleUpdated::$subject` and `$causer` are typed `Model` and `?Model`.
- `Role::syncPermissionIds()` takes an optional causer, ignores ids of other guards, and dispatches only when the permissions changed.

# `Permissions::$runsMigrations`

It's now `Permissions::$runs_migrations`. `Permissions::ignoreMigrations()` works as before.

# Moving your PermissionsSeeder to the registration API

v2 apps seeded permissions with an array in `database/seeders/PermissionsSeeder.php`. Move them to a service provider:

```php
// v2: database/seeders/PermissionsSeeder.php
protected $data = [
    'users' => [
        'view_users' => 'View users',
        'edit_users' => 'Edit users',
    ],
];
```

```php
// v3: app/Providers/AppServiceProvider.php
use Javaabu\Permissions\Permissions;

public function boot(): void
{
    Permissions::register('users', [
        'view_users' => 'View users',
        'edit_users' => 'Edit users',
    ]);
}
```

Set the guard your seeder used (often `web_admin`) in `config/permissions.php`:

```php
'guards' => ['web_admin'],
```

Replace the seeder's body with `$this->call(\Javaabu\Permissions\Database\Seeders\PermissionsSeeder::class)`, or run `php artisan permissions:sync`. If your `RolesSeeder` gave `super_admin` every permission, delete it: the default `roles` config does the same.

# Policies

Your app's policies keep working. You can replace the conventional ones with [`ResourcePolicy`](basic-usage/policies.md#resourcepolicy):

```php
class UserPolicy extends ResourcePolicy
{
    protected string $resource = 'users';

    protected array $abilities = [
        'approve' => 'approve_users',
        'updateRole' => 'approve_users',
    ];
}
```

# Role management

v3 has its own [role routes, controller and pages](basic-usage/managing-roles.md), in Blade (javaabu/forms v2) and Inertia React. Your existing `RolesController` and views keep working. To switch, delete them and register `Route::roles(['guard' => 'web_admin'])` in your admin route group, then set `permissions.layout` to your admin layout. v2's views (`admin.roles.*`) aren't used by the package; copy anything you customized into the published `roles` views.
