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
            "permissions_count": 3,
            "can": { "view": true, "update": true, "delete": false },
            "links": {
                "show": "https://example.test/admin/roles/2",
                "edit": "https://example.test/admin/roles/2/edit",
                "update": "https://example.test/admin/roles/2",
                "destroy": "https://example.test/admin/roles/2"
            },
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
| `GET /roles/create` | `{ "data": { "role": null, "permission_groups": [...], "guard": "web_admin", "translations": null } }` |
| `POST /roles` | `201` with the role and its `permissions` |
| `GET /roles/{role}` | The role and its `permissions` |
| `GET /roles/{role}/edit` | `{ "data": { "role": {...}, "permission_groups": [...], "guard": "…", "translations": null } }` |
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

Labels and descriptions are translated into the current locale. `can` says what the current user may do with each role, and `links` holds the URLs of its pages under the route names you registered. When descriptions are [translated](../advanced-usage/translatable-roles.md), roles also have `translations` (`{ "description_en": …, "description_dv": … }`) and the form data has the inputs for each language.

# Pages

Without JSON, the actions render pages with the stack in `config('permissions.views')`: Blade (the default) or Inertia React. Once saved, `store` and `update` redirect to the edit page and `destroy` to the index, each with a translated `success` flash message.

| Page | Blade view | Inertia page |
| --- | --- | --- |
| List, with search | `permissions::roles.index` | `permissions::Roles/Index` |
| New | `permissions::roles.create` | `permissions::Roles/Create` |
| View, with the granted permissions | `permissions::roles.show` | `permissions::Roles/Show` |
| Edit | `permissions::roles.edit` | `permissions::Roles/Edit` |

The form has the role's name and description, and a card of permission checkboxes for each group, with **Select all** and **Select none**. Buttons only show when the user may use them, and deleting asks first.

## Blade

The views are built with [javaabu/forms](https://github.com/Javaabu/forms) v2 components, so they follow your `forms.framework` (Onu or Bootstrap 6). They extend your layout:

```php
// config/permissions.php
'views' => 'blade',
'layout' => 'layouts.app',     // the view the pages @extends
'layout_section' => 'content', // the section they fill
```

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
<head><title>{{ $title ?? config('app.name') }}</title></head>
<body>
    @yield('content')
</body>
</html>
```

A small script, `permissions::roles` (select all and none, and the delete confirmation), comes through [javaabu/manifest](https://github.com/Javaabu/manifest). The pages require it themselves; add manifest's Vite plugin to your build:

```js
// vite.config.js
import { defineConfig } from "vite"
import laravel from "laravel-vite-plugin"
import manifest from "./vendor/javaabu/manifest/dist/vite.js"

export default defineConfig({
    plugins: [
        laravel({ input: ["resources/css/app.css", "resources/js/app.js"] }),
        manifest(),
    ],
})
```

The script imports no npm packages, so the Blade pages need nothing beyond what javaabu/forms' Blade components need. `php artisan manifest:check` doesn't ask a Blade app for React, `@inertiajs/react` or `@javaabu/forms`: permissions declares those for its React pages only, and the check skips them while the app's `package.json` has no Inertia adapter.

The pages use Tailwind utilities from Onu's theme, logical ones only (`ms-`, `text-start`), so they work right to left. Let Tailwind see them:

```css
/* resources/css/app.css */
@source "../../vendor/javaabu/permissions/resources/views";
```

## Inertia React

```php
// config/permissions.php
'views' => 'inertia',
```

The pages are written in TypeScript with [@javaabu/forms](https://github.com/Javaabu/react-forms) and ship inside the Composer package; javaabu/manifest makes them available to your build. Resolve them with `resolvePage()`:

```tsx
// resources/js/app.tsx
import { createInertiaApp } from "@inertiajs/react"
import { resolvePage } from "@javaabu/manifest/inertia"
import { createRoot } from "react-dom/client"

createInertiaApp({
    resolve: (name) => resolvePage(name, import.meta.glob("./pages/**/*.tsx")),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />)
    },
})
```

Install what the pages need, then check:

```bash
composer require inertiajs/inertia-laravel
php artisan manifest:check   # prints the install command for anything missing
```

They need `@javaabu/forms`, `@javaabu/onu`, `react`, `react-dom` and `@inertiajs/react`, and so do the components you import from `@javaabu/permissions`. `manifest:check` lists them once your `package.json` has `@inertiajs/react`; before that, run `php artisan manifest:check --scope=pages`. Wrap your app in `FormsProvider` with the Onu and Inertia adapters, as @javaabu/forms describes, so forms submit as Inertia visits. Your persistent layout wraps the pages and shows the `success` flash message as it does for your own pages.

The pages' text comes from the server, translated, in the `strings` prop, so they're in Dhivehi when the app's locale is `dv`.

You can use the pages' parts in your own pages:

```tsx
import { PermissionGroupFields, RoleActions, RoleForm } from "@javaabu/permissions"
import type { PermissionGroup, RoleData } from "@javaabu/permissions"

export default function EditRole({ role, permission_groups, translations, strings, urls }) {
    return (
        <RoleForm
            role={role}
            action={urls.update}
            permissionGroups={permission_groups}
            translations={translations}
            strings={strings}
        />
    )
}
```

## Page props

| Prop | Pages | What |
| --- | --- | --- |
| `title` | all | The translated page title |
| `guard` | all | The guard the routes manage |
| `routes` | all | Route names by action, e.g. `routes.edit` is `admin.roles.edit` |
| `urls` | all | `index`, `create` and `store`, plus `show`, `edit`, `update` and `destroy` on a role's pages |
| `can` | all | `{ create, update, delete }` for the current user; `update` and `delete` are about the page's role |
| `strings` | all (Inertia only) | The pages' translated text |
| `roles` | index | A paginator (Blade) or a paginated `RoleResource` collection (Inertia) |
| `search` | index | The search term, or null |
| `role` | create, show, edit | The role model (Blade) or a `RoleResource` array (Inertia); null on create |
| `permission_groups` | create, show, edit | As in the JSON above; on show, only the role's permissions |
| `translations` | create, edit | `{ values, inputs }` when descriptions are [translated](../advanced-usage/translatable-roles.md), otherwise null |

To change the pages, see [Overriding controllers and views](../advanced-usage/overriding-controllers-and-views.md).
