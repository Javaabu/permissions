---
title: Multiple guards
sidebar_position: 2
---

spatie/laravel-permission keeps permissions and roles per guard: `view_exports` on `web` and `view_exports` on `web_admin` are two different permissions. This package keeps that model, and adds config and helpers so that apps with several user models stay manageable.

# Choosing the guards

```php
// config/auth.php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'web_admin' => ['driver' => 'session', 'provider' => 'admins'],
],

// config/permissions.php
'guards' => ['web_admin'],
```

Registered permissions and configured roles are created for each guard in `permissions.guards`, or for the default guard (`auth.defaults.guard`) when it's empty. A group or role can name its own:

```php
Permissions::register('profile', ['edit_own_profile' => 'Edit own profile'])->guards('web');
Permissions::role('member', 'Member', ['edit_own_profile'], guards: 'web');
```

```php
Permissions::defaultGuards();                       // ['web_admin']
Permissions::guardsFor(Permissions::group('profile')); // ['web']
Permissions::allGuards();                           // ['web_admin', 'web']
```

# Which guard a user model uses

spatie works out the guard from the auth config: the guards whose provider uses the model's class. It prefers the default guard if that's one of them.

```php
Permissions::guardFor($admin); // "web_admin"
```

If one model is used by several guards, set the one to use for permissions on the model:

```php
protected string $guard_name = 'web_admin';
```

A user can only get roles and permissions of its own guard. `updateRole()` ignores roles of other guards.

# Checking against a guard

```php
$admin->anyPermission(['view_reports'], 'web_admin');
Permissions::check(['view_reports'], 'web_admin'); // the user signed in on web_admin
```

```blade
@anypermission(['view_reports'], 'web_admin')
    …
@endanypermission
```

In a policy, set `protected ?string $guard = 'web_admin';` on a [`ResourcePolicy`](../basic-usage/policies.md#checking-one-guard).

# Routes per guard

```php
Route::roles(['guard' => 'web_admin', 'prefix' => 'admin/roles', 'name' => 'admin.roles']);
Route::roles(['guard' => 'web', 'prefix' => 'account/roles', 'name' => 'account.roles']);
```

Each set of routes adds `auth:{guard}` middleware and only sees roles and permissions of its guard.

# Querying by guard

```php
Role::query()->forGuard('web_admin')->get();
Permission::query()->forGuard('web_admin')->inGroup('exports')->get();
Permissions::permissions('web_admin'); // registered permissions on that guard
```

# Relating models to users

Don't add a `user_id` column that assumes `App\Models\User`. Use a morph (`$table->nullableMorphs('owner')`), so admins and users can both own records. See [owned resources](../basic-usage/policies.md#owned-resources).
