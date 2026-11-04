---
name: javaabu-permissions
description: Register permissions, roles and policies with javaabu/permissions (built on spatie/laravel-permission) in a Laravel app or package. Use whenever the project depends on javaabu/permissions and the task adds a permission, a policy or an ability, defines or assigns roles, checks permissions in PHP or Blade, registers the role management routes, overrides a package's policy or controller, or involves several guards or user models.
license: MIT
metadata:
  author: Javaabu
---

# Permissions with javaabu/permissions

Permissions, roles and default policies are declared in code, in a service provider's `boot()`. `php artisan permissions:sync` writes them to the database. Everything from spatie/laravel-permission still works (`can()`, `assignRole()`, `hasRole()`, middleware). The docs ship with the package: `vendor/javaabu/permissions/llms.txt` lists every page in `vendor/javaabu/permissions/docs/`.

## Adding permissions for a model

```php
use Javaabu\Permissions\Permissions;

public function boot(): void
{
    // view_exports, edit_exports, delete_exports, force_delete_exports
    Permissions::resource('exports', 'exports', label: 'Exports');

    Permissions::register('exports', [
        'download_exports' => 'Download exports',
    ]);
}
```

Then run `php artisan permissions:sync`, and add the Dhivehi for every description and label to `lang/dv.json`:

```json
{
    "Exports": "އެކްސްޕޯޓްތައް",
    "Download exports": "އެކްސްޕޯޓްތައް ޑައުންލޯޑުކުރުން"
}
```

## Writing a policy

Extend `ResourcePolicy` for the conventional abilities. Map extra abilities in `$abilities`:

```php
namespace App\Policies;

use Javaabu\Permissions\Policies\ResourcePolicy;

class ExportPolicy extends ResourcePolicy
{
    protected string $resource = 'exports';

    protected array $abilities = [
        'download' => 'download_exports',
    ];
}
```

| Ability | Permission |
| --- | --- |
| `viewAny`, `view` | `view_exports` |
| `create`, `update` | `edit_exports` |
| `delete` | `delete_exports` |
| `restore`, `viewTrash` | `delete_exports` or `force_delete_exports` |
| `forceDelete` | `force_delete_exports` |

For records users own, set `protected bool $owned = true;`. `update` and `delete` then need `edit_others_x` / `delete_others_x`, or `edit_x` / `delete_x` plus `$model->isOwnedBy($user)`. Write a method for anything that isn't just a permission; inside it, `$this->allows($user, 'perm')` checks any of the given permissions.

In an app, register the policy as usual (`Gate::policy()`, or Laravel's discovery). **In a package, use `Permissions::policy(Export::class, ExportPolicy::class)`**, so the app can override it.

## Rules

- Name permissions `{action}_{resource}`, with the resource in snake_case plural: `view_blog_posts`, `edit_others_oauth_clients`.
- Never `Permission::create()` or `firstOrCreate()` permissions in migrations, seeders or controllers. Register them, and sync.
- Never `Gate::policy()` from a package. Apps override package policies with `config('permissions.policies')` or `Gate::policy()`.
- Type-hint users as `Illuminate\Database\Eloquent\Model` in policies and helpers. Don't assume `App\Models\User` or the `web` guard.
- Relate records to users with a morph (`owner_type`/`owner_id`), not a `user_id` that assumes one model.
- User models use `Javaabu\Permissions\Traits\HasRoles`.
- Authorize with policies (`$this->authorize('update', $export)`) rather than checking permission names in controllers.
- Write descriptions as short English sentences ("View exports"). They're translation keys.

## Roles

Roles the app always needs go in `config/permissions.php`; the sync creates them for every guard:

```php
'roles' => [
    'super_admin' => ['description' => 'Super Admin', 'permissions' => '*'],
    'editor' => ['description' => 'Editor', 'permissions' => ['view_*', 'edit_posts']],
],
```

`'*'` keeps a role synced to every permission. A list of names and `Str::is()` patterns only ever adds permissions. To change roles from a form, use `$user->updateRole($roles, causer: $request->user())`, which checks the causer's `updateRole` ability on the user class.

## Checking permissions

```php
$user->can('view_exports');
$user->anyPermission(['edit_exports', 'delete_exports']);
$user->anyPermission(['view_reports'], 'web_admin'); // that guard only
```

```blade
@can('update', $export) … @endcan
@anypermission(['edit_exports', 'delete_exports']) … @endanypermission
```

## Role management routes

They aren't loaded automatically. Register them inside the app's admin group, with the guard they manage:

```php
Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::roles(['guard' => 'web_admin']); // admin.roles.index, …; adds auth:web_admin
});
```

Every action also answers JSON. Pages render with `config('permissions.views')`, which is `blade` or `inertia`. Override the controller with `config('permissions.controllers.roles')` and a subclass of `Javaabu\Permissions\Http\Controllers\RolesController`.

## Multiple guards

Set `'guards' => ['web_admin']` (or several) in `config/permissions.php`. Registered permissions and configured roles are created for each one. Limit a group with `->guards('web_admin')`. A user only gets roles of its own guard; use `Permissions::guardFor($user)` to see which.

## Tests

Sync in `setUp()` (`$this->artisan('permissions:sync')`), then give users roles or permissions. Test both guards when the feature is used by more than one user model.

More: [references/package-authors.md](references/package-authors.md) for writing a package that ships permissions and policies, and [references/common-mistakes.md](references/common-mistakes.md).
