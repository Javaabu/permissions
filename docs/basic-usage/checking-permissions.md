---
title: Checking permissions
sidebar_position: 5
---

Permissions are registered on the gate by spatie/laravel-permission, so everything Laravel offers works:

```php
$user->can('view_exports');
Gate::allows('view_exports');
$this->authorize('update', $export);
```

```blade
@can('view_exports')
    <a href="{{ route('admin.exports.index') }}">{{ __('Exports') }}</a>
@endcan
```

Spatie's middleware works too, as long as you register its aliases in `bootstrap/app.php`:

```php
Route::get('/exports', ExportsController::class)->middleware('permission:view_exports,web_admin');
```

# Any of several permissions

```php
$user->anyPermission(['edit_exports', 'delete_exports']);              // through the gate
$user->anyPermission(['edit_exports', 'delete_exports'], 'web_admin'); // only that guard's permissions
```

Through the gate, `Gate::before()` callbacks apply. With a guard, permissions that don't exist on that guard count as missing.

```blade
@anypermission(['edit_exports', 'delete_exports'])
    …
@endanypermission

@anypermission(['view_reports'], 'web_admin')
    …
@endanypermission
```

The directive checks the user signed in on the given guard, or on the default guard. The same check is available in PHP:

```php
use Javaabu\Permissions\Permissions;

Permissions::check(['view_reports'], 'web_admin'); // false for guests
```
