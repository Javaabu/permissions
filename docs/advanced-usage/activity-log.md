---
title: Activity log
sidebar_position: 5
---

When [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) v4 or v5 is installed, the package logs role changes:

| Description | Subject | Changes |
| --- | --- | --- |
| `role_updated` | The user whose roles changed (`updateRole()`) | `attributes.role`, `old.role` |
| `permissions_updated` | The role whose permissions changed (`syncPermissionIds()`, the role form) | `attributes.permissions`, `old.permissions` |

The causer is the person who made the change. The logs have no `event`; tell them apart by description.

Where the changes go depends on the version:

- **v5** keeps model changes in the `attribute_changes` column, so the package writes them there (`withChanges()`), and `properties` stays empty. Diffs and descriptions treat them like any other model change.
- **v4** has no such column, so they go in `properties`, the way v4 logs its own model changes. `$activity->changes()` reads them.

[javaabu/activitylog](https://github.com/Javaabu/activitylog) v2 is built on v5 and installs it. It also gives `role_updated` and `permissions_updated` their own tags and readable descriptions.

```bash
composer require spatie/laravel-activitylog:"^5.0"
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

v2's `Role` model logged its own attribute changes through javaabu/activitylog. v3's doesn't, so the package doesn't depend on an activity log. To log them, extend the model. With spatie/laravel-activitylog v5:

```php
namespace App\Models;

use Javaabu\Permissions\Models\Role as BaseRole;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Role extends BaseRole
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
```

On v4 the imports are `Spatie\Activitylog\LogOptions` and `Spatie\Activitylog\Traits\LogsActivity`.

With javaabu/activitylog v2, use its trait instead. Its defaults already leave out hidden attributes and timestamps, so you don't need `getActivitylogOptions()`:

```php
namespace App\Models;

use Javaabu\Activitylog\Traits\LogsActivity;
use Javaabu\Permissions\Models\Role as BaseRole;

class Role extends BaseRole
{
    use LogsActivity;
}
```

Then point spatie/laravel-permission at your model:

```php
// config/permission.php
'models' => [
    'role' => App\Models\Role::class,
],
```
