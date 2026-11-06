---
title: Roles
sidebar_position: 3
---

# Defining roles

The sync creates the roles in `config/permissions.php`, for every guard in `permissions.guards`:

```php
'roles' => [
    'super_admin' => [
        'description' => 'Super Admin',
        'permissions' => '*',
    ],
    'editor' => [
        'description' => 'Editor',
        'permissions' => ['view_*', 'edit_posts'],
    ],
    'auditor' => [
        'description' => 'Auditor',
        'permissions' => ['view_*'],
        'guards' => ['web_admin'],
    ],
],
```

- `permissions` is either `'*'`, for every permission of the guard, kept in sync, or a list of names and [`Str::is()`](https://laravel.com/docs/strings#method-str-is) patterns. A list only ever adds permissions, so admins can grant more from the role form.
- `description` is an English string and a translation key. It defaults to a headline of the name.
- `guards` defaults to `permissions.guards`.

`super_admin` with `'*'` is the default. Removing it from the config doesn't delete the role.

You can also define roles in code. Config entries with the same name replace them:

```php
Permissions::role('auditor', 'Auditor', ['view_*'], guards: 'web_admin');
```

# Assigning roles

Roles come from spatie/laravel-permission, so `assignRole()`, `syncRoles()`, `hasRole()` and the rest work as usual. A role belongs to a guard, and a user can only get roles of its own guard.

```php
$user->assignRole('editor');
$user->role; // the first role, or null
```

`updateRole()` changes the roles from a form, checking that the person making the change may:

```php
$user->updateRole($request->input('role'), causer: $request->user());
```

- The causer may change roles when it passes the `updateRole` ability for the user model's class. Define that ability in the model's policy.
- When it may not, nothing changes. The exception is a new user (`new: true`), who gets `config('permissions.default_role')`, or the `default_role` you pass.
- Roles that don't exist for the user's guard are ignored. If none of them exist, nothing changes.
- A causer that may change roles can pass `[]` to remove every role.
- The causer defaults to the signed-in user.
- When the roles change, it dispatches `UserRoleUpdated`.

```php
$user->updateRole('admin', new: true, default_role: 'member');
```

# Changing a role's permissions

`syncPermissionIds()` replaces a role's permissions with the given ids. Ids of other guards are ignored. When the permissions change, it dispatches `RolePermissionsUpdated`:

```php
$role->syncPermissionIds($request->input('permissions', []), causer: $request->user());
```

# Querying

```php
use Javaabu\Permissions\Models\Role;

Role::query()->forGuard('web_admin')->get();
Role::query()->search('editor')->get(); // name or description
$role->translated_description;          // __($role->description)

User::query()->whereRoleHasPermissions(['view_exports', 'edit_exports'])->get();
```

`whereRoleHasPermissions()` finds models with a role that has all the given permissions. Spatie's `permission()` scope also counts direct permissions, and matches any of them.

# Events

| Event | When | Properties |
| --- | --- | --- |
| `Javaabu\Permissions\Events\UserRoleUpdated` | `updateRole()` changed a model's roles | `old_role`, `new_role` (name arrays), `subject`, `causer` |
| `Javaabu\Permissions\Events\RolePermissionsUpdated` | `syncPermissionIds()` changed a role's permissions | `old_permissions`, `new_permissions` (name arrays), `role`, `causer` |

With spatie/laravel-activitylog installed, both events are [logged](../advanced-usage/activity-log.md).
