---
title: Overriding controllers and views
sidebar_position: 4
---

# Controllers

Extend `RolesController` and point the package at your class, either from config:

```php
// config/permissions.php
'controllers' => [
    'roles' => App\Http\Controllers\Admin\RolesController::class,
],
```

or for one set of routes:

```php
Route::roles(['guard' => 'web_admin', 'controller' => App\Http\Controllers\Admin\RolesController::class]);
```

or by binding it in the container, which also affects routes registered before your provider boots:

```php
$this->app->bind(
    \Javaabu\Permissions\Http\Controllers\RolesController::class,
    \App\Http\Controllers\Admin\RolesController::class,
);
```

A subclass can override any action, or one of these helpers:

| Method | What |
| --- | --- |
| `query(Request $request): Builder` | The roles the routes can see; scoped to the guard |
| `findRole(Request $request, string $role): Model` | Finds a role or 404s |
| `form(Request $request, ?Model $role)` | Renders the create and edit pages |
| `attributes(RoleRequest $request): array` | The validated attributes saved on the role |
| `meta(Request $request, ?Model $role = null): array` | The props every page gets: `guard`, `routes`, `urls`, `can`, `strings` |
| `strings(): array` | The translated text of the Inertia pages |
| `routeNames(Request $request): array` | The route names passed to pages |

```php
namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Javaabu\Permissions\Http\Controllers\RolesController as BaseRolesController;

class RolesController extends BaseRolesController
{
    protected function query(Request $request): Builder
    {
        return parent::query($request)->where('name', '!=', 'super_admin');
    }
}
```

`RoleRoutes::guard($request)` returns the guard of the current route.

# Choosing the stack

```php
// config/permissions.php
'views' => 'inertia', // or 'blade'
```

Requests that expect JSON always get JSON. Setting up each stack is described in [Managing roles](../basic-usage/managing-roles.md#pages).

# Blade views

Publish the views and edit the copies:

```bash
php artisan vendor:publish --tag=permissions-views
```

Laravel uses `resources/views/vendor/permissions/roles/index.blade.php` in place of the package's. Override one view and leave the rest: `roles.index`, `roles.create`, `roles.edit`, `roles.show`, and the partials `roles.partials.form`, `roles.partials.row`, `roles.partials.actions`, `roles.partials.alerts` and `roles.partials.translatable-description`. Keep the `data-permission-group` and `data-permission-group-select` attributes for select all and none, and `data-confirm` on forms that should ask first. The [page props](../basic-usage/managing-roles.md#page-props) list what each view gets.

# Inertia pages

Copy a page into your app and edit it:

```bash
php artisan manifest:override permissions js/Pages/Roles/Edit.tsx
```

It goes to `resources/vendor/permissions/js/Pages/Roles/Edit.tsx`, and `resolvePage()` uses it instead of the package's. Components work the same way (`js/components/RoleForm.tsx`), and the pages pick up your copy.

To give the pages more props, override `meta()` or an action in your controller.
