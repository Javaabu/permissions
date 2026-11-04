---
title: Activity log
sidebar_position: 5
---

When [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) is installed, the package logs role changes:

| Description | Subject | Properties |
| --- | --- | --- |
| `role_updated` | The user whose roles changed (`updateRole()`) | `attributes.role`, `old.role` |
| `permissions_updated` | The role whose permissions changed (`syncPermissionIds()`, the role form) | `attributes.permissions`, `old.permissions` |

The causer is the person who made the change.

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

There's nothing else to set up: the package checks for `activity()` and adds its listeners when it finds it. To turn logging off:

```php
// config/permissions.php
'log_activity' => false,
```

Without the package, nothing is logged, but the `UserRoleUpdated` and `RolePermissionsUpdated` events still fire, so you can listen to them yourself.

# Logging changes to the role itself

v2's `Role` model logged its own attribute changes through javaabu/activitylog. v3's doesn't, so the package doesn't depend on an activity log. To log them, extend the model:

```php
namespace App\Models;

use Javaabu\Permissions\Models\Role as BaseRole;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends BaseRole
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
```

```php
// config/permission.php
'models' => [
    'role' => App\Models\Role::class,
],
```
