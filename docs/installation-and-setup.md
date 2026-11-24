---
title: Installation & Setup
sidebar_position: 1.2
---

# Installing

```bash
composer require javaabu/permissions
```

The service provider is discovered automatically, and so are those of spatie/laravel-permission, javaabu/forms and javaabu/manifest. Until javaabu/forms v2 and javaabu/manifest are on Packagist, add their repositories first (see [Requirements](requirements.md)).

# Migrating

The package loads its migrations itself. They create spatie's tables, plus a `description` column on `roles` and `permissions`, a `model` column (the permission group) on `permissions`, and the nullable `translations` and `lang` columns on `roles` for [translatable descriptions](advanced-usage/translatable-roles.md).

```bash
php artisan migrate
```

To change the migrations, publish them and stop the package from loading its own copy:

```bash
php artisan vendor:publish --tag=permissions-migrations
```

```php
// AppServiceProvider::register()
\Javaabu\Permissions\Permissions::ignoreMigrations();
```

# Adding roles to your user models

Use the package's `HasRoles` trait, which includes spatie's, on every model that gets roles:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Javaabu\Permissions\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

# Creating the permissions

Permissions and roles are declared in code, by this package and by any package that uses it. Write them to the database:

```bash
php artisan permissions:sync
```

Run it on every deploy, after `migrate`. It's safe to run again: it only creates what's missing and updates what's changed. See [Syncing permissions](basic-usage/syncing-permissions.md).

With no configuration, you get a `super_admin` role on the default guard that has every permission:

```php
$user->assignRole('super_admin');
$user->can('view_roles'); // true
```

# Publishing the config

This is optional. Everything has a default, and the [configuration page](advanced-usage/configuration.md) lists every option.

```bash
php artisan vendor:publish --tag=permissions-config
```

This publishes `config/permissions.php`, the package's own options. spatie/laravel-permission's options stay in `config/permission.php`, which the package preconfigures to use its own `Role` and `Permission` models. To change those, publish them as well:

```bash
php artisan vendor:publish --tag=permissions-spatie-config
```

# Other publishable files

| Tag | What |
| --- | --- |
| `permissions-config` | `config/permissions.php` |
| `permissions-spatie-config` | `config/permission.php` (spatie's options) |
| `permissions-migrations` | The migrations |
| `permissions-views` | The Blade views, in `resources/views/vendor/permissions` |
