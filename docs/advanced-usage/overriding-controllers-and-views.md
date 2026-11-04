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

# Blade views

Publish the views and edit the copies:

```bash
php artisan vendor:publish --tag=permissions-views
```

Laravel uses `resources/views/vendor/permissions/roles/index.blade.php` in place of the package's. You can override one view and leave the rest. The [page props](../basic-usage/managing-roles.md#pages) list what each view gets.

# Inertia pages

The pages render as `permissions::Roles/Index`, `permissions::Roles/Create`, `permissions::Roles/Show` and `permissions::Roles/Edit`. Your Inertia page resolver decides which component each name loads. To use your own page, resolve the name to your component before the package's:

```ts
// resources/js/app.tsx
resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });

    if (name === 'permissions::Roles/Edit') {
        return pages['./Pages/Admin/Roles/Edit.tsx'];
    }

    return pages[`./Pages/${name}.tsx`];
},
```
