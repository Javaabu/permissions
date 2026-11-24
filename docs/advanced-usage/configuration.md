---
title: Configuration
sidebar_position: 3
---

`config/permissions.php` holds this package's options. Publish it with:

```bash
php artisan vendor:publish --tag=permissions-config
```

| Key | Default | What |
| --- | --- | --- |
| `guards` | `[]` | Guards that registered permissions and configured roles are created for. When empty, `auth.defaults.guard` is used. See [Multiple guards](multi-auth.md). |
| `roles` | `super_admin` with `*` | Roles the sync creates: `name => ['description' => …, 'permissions' => '*' or [names and patterns], 'guards' => [...]]`. See [Roles](../basic-usage/roles.md). |
| `default_role` | `null` | The role `updateRole()` gives a new user when the causer can't choose one. |
| `policies` | `[]` | `Model::class => Policy::class` overrides for package policies. See [Policies](../basic-usage/policies.md). |
| `log_activity` | `true` | Log role and permission changes, when spatie/laravel-activitylog is installed. See [Activity log](activity-log.md). |
| `register_defaults` | `true` | Register the package's own `view_roles`, `edit_roles` and `delete_roles` permissions and its role policy. |
| `views` | `env('PERMISSIONS_VIEWS', 'blade')` | Which stack the role pages render with: `blade` or `inertia`. See [Managing roles](../basic-usage/managing-roles.md#pages). |
| `layout` | `'layouts.app'` | The layout the Blade pages `@extends`. |
| `layout_section` | `'content'` | The section of the layout the Blade pages fill. |
| `controllers.roles` | `RolesController::class` | The controller `Route::roles()` uses. |

# spatie/laravel-permission's options

`config/permission.php` holds spatie's options. The package merges in its own copy, which sets:

```php
'models' => [
    'permission' => Javaabu\Permissions\Models\Permission::class,
    'role' => Javaabu\Permissions\Models\Role::class,
],
```

To translate role descriptions, set `role` to `Javaabu\Permissions\Models\Translatable\Role` (see [Translatable role descriptions](translatable-roles.md)). If you use your own models, extend these, since the sync, controller and pages rely on their scopes and attributes. Spatie's options, such as `teams`, `cache` and `enable_wildcard_permission`, are documented in [spatie's docs](https://spatie.be/docs/laravel-permission). Publish them with:

```bash
php artisan vendor:publish --tag=permissions-spatie-config
```
