---
title: Managing roles
sidebar_position: 6
---

The package has routes, a controller and pages for listing, creating, editing and deleting roles and choosing their permissions.

# Registering the routes

Routes aren't loaded automatically. Register them where you want them, with the guard they manage roles for:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::roles(['guard' => 'web_admin']);
});
```

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET | `admin/roles` | `admin.roles.index` | `index` |
| GET | `admin/roles/create` | `admin.roles.create` | `create` |
| POST | `admin/roles` | `admin.roles.store` | `store` |
| GET | `admin/roles/{role}` | `admin.roles.show` | `show` |
| GET | `admin/roles/{role}/edit` | `admin.roles.edit` | `edit` |
| PUT/PATCH | `admin/roles/{role}` | `admin.roles.update` | `update` |
| DELETE | `admin/roles/{role}` | `admin.roles.destroy` | `destroy` |

Options:

| Option | Default | What |
| --- | --- | --- |
| `guard` | the first of `permissions.guards`, or the default guard | The guard whose roles the routes manage |
| `prefix` | `roles` | URI prefix |
| `name` | `roles` | Route name prefix, inside any group name |
| `middleware` | `['auth:{guard}']` | Middleware for the routes. Pass your own to replace it |
| `controller` | `config('permissions.controllers.roles')` | The controller class |

Register the routes once per guard to manage roles of several guards:

```php
Route::roles(['guard' => 'web', 'prefix' => 'account/roles', 'name' => 'account.roles']);
```

Each set of routes only sees roles of its own guard; a role of another guard is a 404.

# Authorization

Every action is authorized with the [role policy](policies.md#the-role-policy):

| Action | Ability | Permission |
| --- | --- | --- |
| `index` | `viewAny` | `view_roles` |
| `show` | `view` | `view_roles` |
| `create`, `store` | `create` | `edit_roles` |
| `edit`, `update` | `update` | `edit_roles` |
| `destroy` | `delete` | `delete_roles` |

# Validation

`store` and `update` validate with `Javaabu\Permissions\Http\Requests\RoleRequest`:

- `name`: required when creating, ASCII letters, numbers, dashes and underscores, and unique among roles of the guard.
- `description`: required when creating, up to 255 characters, any language.
- `permissions`: ids of permissions of the guard.

On a form submission (not JSON), a missing `permissions` field means no permission is ticked, so the role loses all its permissions. A JSON update without `permissions` leaves them alone.

# JSON API

Every action answers JSON when the request expects it (`Accept: application/json`). That gives you an API for single-page apps and scripts:

```bash
curl -H 'Accept: application/json' https://example.test/admin/roles?search=editor
```

```json
{
    "data": [
        {
            "id": 2,
            "name": "editor",
            "description": "ލިޔުންތައް ބަދަލުކުރާ މީހުން",
            "translated_description": "ލިޔުންތައް ބަދަލުކުރާ މީހުން",
            "guard_name": "web_admin",
            "created_at": "2026-11-04T10:00:00+05:00",
            "updated_at": "2026-11-04T10:00:00+05:00"
        }
    ],
    "links": { "…": "…" },
    "meta": { "current_page": 1, "per_page": 20, "total": 1, "…": "…" }
}
```

| Request | Response |
| --- | --- |
| `GET /roles?search=&per_page=` | Paginated `RoleResource`s, up to 100 per page |
| `GET /roles/create` | `{ "data": { "role": null, "permission_groups": [...], "guard": "web_admin" } }` |
| `POST /roles` | `201` with the role and its `permissions` |
| `GET /roles/{role}` | The role and its `permissions` |
| `GET /roles/{role}/edit` | `{ "data": { "role": {...}, "permission_groups": [...], "guard": "…" } }` |
| `PUT/PATCH /roles/{role}` | The updated role |
| `DELETE /roles/{role}` | `204` |

`permission_groups` is a list of groups, in the order they were registered:

```json
{
    "name": "exports",
    "label": "އެކްސްޕޯޓްތައް",
    "permissions": [
        { "id": 7, "name": "view_exports", "description": "އެކްސްޕޯޓްތައް ބެލުން", "granted": true }
    ]
}
```

Labels and descriptions are translated into the current locale.

# Pages

Without JSON, the actions render pages with the stack in `config('permissions.views')`: Blade views (`permissions::roles.index`) or Inertia pages (`permissions::Roles/Index`). Once saved, `store` and `update` redirect to the edit page and `destroy` to the index, each with a translated `success` flash message.

| Page | Blade view | Inertia page |
| --- | --- | --- |
| List | `permissions::roles.index` | `permissions::Roles/Index` |
| New | `permissions::roles.create` | `permissions::Roles/Create` |
| View | `permissions::roles.show` | `permissions::Roles/Show` |
| Edit | `permissions::roles.edit` | `permissions::Roles/Edit` |

Each page gets:

| Prop | Pages | What |
| --- | --- | --- |
| `title` | all | The translated page title |
| `guard` | all | The guard the routes manage |
| `routes` | all | Route names by action, e.g. `routes.edit` is `admin.roles.edit` |
| `roles` | index | A paginator (Blade) or a paginated `RoleResource` collection (Inertia) |
| `search` | index | The search term, or null |
| `role` | create, show, edit | The role model (Blade) or a `RoleResource` array (Inertia); null on create |
| `permission_groups` | create, show, edit | As in the JSON above; on show, only the role's permissions |

To change the pages, see [Overriding controllers and views](../advanced-usage/overriding-controllers-and-views.md).
