---
title: API reference
sidebar_position: 7
---

# `Javaabu\Permissions\Permissions`

A facade over the `PermissionRegistry` singleton, plus a few static helpers.

| Method | Returns | What |
| --- | --- | --- |
| `register(string $group, array $permissions = [], ?string $label = null)` | `PermissionGroup` | Add `name => description` pairs (or names) to a group |
| `resource(string $group, ?string $noun = null, array $actions = ['view', 'edit', 'delete', 'force_delete'], ?string $label = null)` | `PermissionGroup` | Register `{action}_{group}` permissions |
| `role(string $name, ?string $description = null, string\|array $permissions = [], array\|string\|null $guards = null)` | `RoleDefinition` | Define a role the sync creates |
| `policy(string $model, string $policy)` | `PermissionRegistry` | Register a default policy |
| `sync(?array $guards = null, bool $prune = false)` | `SyncResult` | Write permissions and roles to the database |
| `group(string $name)` | `?PermissionGroup` | A registered group |
| `groups()` | `array<string, PermissionGroup>` | Every group, in registration order |
| `permissions(?string $guard = null)` | `array<string, string>` | Registered `name => description`, optionally for one guard |
| `has(string $permission)` | `bool` | Whether a permission is registered |
| `description(string $permission)` | `?string` | Its English description |
| `groupOf(string $permission)` | `?PermissionGroup` | The group it's in |
| `label(string $group)` | `string` | The untranslated label of a group |
| `roles()` | `array<string, RoleDefinition>` | Roles from code, overridden by config |
| `policies()` | `array<class-string, class-string>` | Package policies, overridden by config |
| `applyPolicies(?Gate $gate = null)` | `void` | Add the policies to the gate (done for you after boot) |
| `defaultGuards()` | `array<int, string>` | `permissions.guards`, or the default guard |
| `guardsFor(PermissionGroup\|RoleDefinition $definition)` | `array<int, string>` | The guards a group or role uses |
| `allGuards()` | `array<int, string>` | Every guard any group or role uses |
| `guardFor(Model $user)` | `string` | The guard spatie uses for a user model |
| `check(string\|array $permissions, ?string $guard = null)` | `bool` | Whether the user signed in on the guard has any of them |
| `permissionName(string $action, string $resource)` | `string` | `edit` + `exports` = `edit_exports` |
| `ignoreMigrations()` | `void` | Don't load the package's migrations |
| `flush()` | `void` | Forget everything registered (for tests) |

# `PermissionGroup`

| Method | What |
| --- | --- |
| `add(array $permissions)` | Add permissions |
| `label(string $label)` | Set the label |
| `guards(array\|string $guards)` | Limit the group to these guards |
| `grantTo(array\|string $roles, ?array $only = null)` | Give existing roles the group's permissions on sync |
| `getPermissions()`, `getLabel()`, `getGuards()`, `getGrants()`, `has(string $permission)` | Read it back |
| `$name` | The group name |

# `RoleDefinition`

`$name`, `$description`, `$permissions`, `$guards`, `getDescription()`, `grantsEverything()`, `resolvePermissions(array $available)`.

# `Javaabu\Permissions\Policies\ResourcePolicy`

| Member | What |
| --- | --- |
| `protected string $resource` | The resource in permission names |
| `protected ?string $guard = null` | Check this guard's permissions |
| `protected bool $owned = false` | Use `edit_others_x` / `delete_others_x` for models the user doesn't own |
| `protected array $abilities = []` | Custom `ability => permission(s)` |
| `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`, `viewTrash` | The standard abilities |
| `permission(string $action): string` | The permission name for an action |
| `protected allows(Model $user, string ...$permissions): bool` | Any of the permissions |
| `protected isOwner(Model $user, Model $model): bool` | Calls `$model->isOwnedBy($user)` |

# `Javaabu\Permissions\Traits\HasRoles`

Includes `Spatie\Permission\Traits\HasRoles`.

| Method | What |
| --- | --- |
| `anyPermission(string\|array $permissions, ?string $guard = null): bool` | Any of the permissions |
| `updateRole(string\|array $role, bool $new = false, ?Model $causer = null, ?string $default_role = null): void` | Change roles if the causer may |
| `$model->role` | The first role |
| `whereRoleHasPermissions(string\|array $permissions)` | Scope: models whose roles have all of them |

# `Javaabu\Permissions\Models\Role`

Extends spatie's `Role`. Scopes `forGuard(string $guard)` and `search(?string $search)`; `translated_description`; `syncPermissionIds(array $permission_ids, ?Model $causer = null)`; `Role::factory()`.

# `Javaabu\Permissions\Models\Permission`

Extends spatie's `Permission`. Scopes `forGuard(string $guard)` and `inGroup(string $group)`; `translated_description`; `group_label`; `Permission::factory()`. The `model` column is the group.

# Routing and HTTP

| Class | What |
| --- | --- |
| `Route::roles(array $options = [])` | Registers the role routes |
| `Javaabu\Permissions\Routing\RoleRoutes` | `register()`, `guard(Request)`, `namePrefix(Request)`, `isRoleRoute(Request)` |
| `Javaabu\Permissions\Http\Controllers\RolesController` | The role controller |
| `Javaabu\Permissions\Http\Requests\RoleRequest` | Its validation; `role()` is the role being updated |
| `Javaabu\Permissions\Http\Resources\RoleResource`, `PermissionResource` | JSON shapes; roles include `can`, `links` (on role routes), `permissions_count` (when counted) and `translations` (when translated) |
| `Javaabu\Permissions\Support\Translations` | Optional translatable support: `enabled()`, `supports()`, `supportsRole()`, `rules()`, `values()`, `form()`. See [Translatable role descriptions](translatable-roles.md) |
| `Javaabu\Permissions\Models\Translatable\Role` | A role with a translatable `description` |
| `Javaabu\Permissions\Support\PermissionGroups` | `forGuard(string $guard, ?Model $role = null)`, `forRole(Model $role)` |
| `Javaabu\Permissions\Views\ViewRenderer` | `render(string $page, array $data)`, `stack()`, `isInertia()`, `inertiaPage(string $page)` |

# Front end

| Asset | What |
| --- | --- |
| `permissions::roles` | Script of the Blade pages: `data-permission-group-select` buttons and `data-confirm` forms |
| `permissions::Roles/Index`, `Create`, `Edit`, `Show` | The Inertia pages |
| `@javaabu/permissions` | `RoleForm`, `PermissionGroupFields`, `RoleActions` and the types (`RoleData`, `PermissionGroup`, `RolesStrings`, …) |

# Console and seeders

| | What |
| --- | --- |
| `php artisan permissions:sync {--guard=*} {--prune}` | Sync permissions and roles |
| `Javaabu\Permissions\Database\Seeders\PermissionsSeeder` | The same, as a seeder |

# Blade

| Directive | What |
| --- | --- |
| `@anypermission($permissions, $guard = null)` … `@endanypermission` | The signed-in user has any of the permissions |

# Events

`Javaabu\Permissions\Events\UserRoleUpdated` and `RolePermissionsUpdated`. See [Roles](../basic-usage/roles.md#events).
