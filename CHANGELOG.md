# Changelog

All notable changes will be documented in this file. See [docs/upgrading.md](docs/upgrading.md) for moving from v2.

## 3.0.0 - unreleased

- Permission registration API: `Permissions::register()`, `resource()`, `role()`, grouped permissions with translatable descriptions and labels
- `php artisan permissions:sync` (with `--guard` and `--prune`), `PermissionsSeeder` and `Permissions::sync()`
- Overridable package policies: `Permissions::policy()`, `config('permissions.policies')`, and `Gate::policy()` in the app
- `ResourcePolicy` base class with custom abilities, ownership and guard support; `RolePolicy`
- Multi-auth: `permissions.guards`, per-group and per-role guards, `Permissions::guardFor()`, `Permissions::check()`, guard-aware `anyPermission()` and `@anypermission`
- Role management: `Route::roles()`, an overridable `RolesController` with a JSON API
- Role pages in Blade (javaabu/forms v2) and Inertia React (@javaabu/forms, shipped through javaabu/manifest), right-to-left ready, with select all per permission group; `permissions.layout` and `layout_section` options
- npm dependencies (`@javaabu/forms`, `@javaabu/onu`, React, `@inertiajs/react`) are declared for the React pages only, so `php artisan manifest:check` doesn't ask Blade apps for them
- React components for apps: `RoleForm`, `PermissionGroupFields`, `RoleActions` from `@javaabu/permissions`
- `RoleResource` includes `can`, `links` and `permissions_count`
- Optional javaabu/translatable support for role descriptions: `Models\Translatable\Role`, `Support\Translations`, and a migration adding `translations` and `lang` to the roles table
- Dhivehi translations
- Activity logging is optional, through spatie/laravel-activitylog v4 or v5; on v5 the changes go in `attribute_changes`
- Requires PHP 8.4, Laravel 12 or 13, javaabu/forms v2 and javaabu/manifest
- Breaking: no longer requires javaabu/helpers or javaabu/activitylog; `Role` no longer implements `AdminModel` or uses `LogsActivity`; factories moved to `Javaabu\Permissions\Database\Factories`; `updateRole()` no longer reads `get_setting()`
