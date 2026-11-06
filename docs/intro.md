---
title: Introduction
sidebar_position: 1.0
---

# Permissions

[javaabu/permissions](https://github.com/Javaabu/permissions) adds what Javaabu apps and packages need on top of [spatie/laravel-permission](https://spatie.be/docs/laravel-permission):

- **A registration API.** Apps and packages declare permissions, roles and policies in code, and `php artisan permissions:sync` writes them to the database.
- **Grouped, translatable permissions.** Every permission has a group (such as `exports`) and an English description that doubles as a translation key, so the role form can show "ބެލުން" next to `view_exports`.
- **Overridable policies.** A package ships a default policy; the app replaces it from config or with `Gate::policy()`. A `ResourcePolicy` base class maps `viewAny`, `update`, `forceDelete` and friends to permission names.
- **Multi-auth.** Permissions and roles per guard, any number of user models, and helpers that check against a specific guard.
- **Role management.** Routes, an overridable controller, a JSON API, and Blade or Inertia React pages.

```php
use Javaabu\Permissions\Permissions;

// in a service provider's boot()
Permissions::resource('exports', 'exports', label: 'Exports');
Permissions::policy(Export::class, ExportPolicy::class);
```

```bash
php artisan permissions:sync
```

Start with [Installation & Setup](installation-and-setup.md). If you're writing a package, go to [For package authors](advanced-usage/for-package-authors.md). If you're coming from v2, read [Upgrading](upgrading.md).
