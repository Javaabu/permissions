---
title: Registering permissions
sidebar_position: 1
---

Permissions are declared in code, in a service provider's `boot()` method, and written to the database by [`permissions:sync`](syncing-permissions.md). Registering doesn't touch the database, so it's cheap to do on every request.

# Groups

Every permission belongs to a group, usually the model or the package it's for. The group is stored in the `model` column of `permissions`, and the role form shows one section per group.

```php
use Javaabu\Permissions\Permissions;

public function boot(): void
{
    Permissions::register('exports', [
        'view_exports' => 'View exports',
        'download_exports' => 'Download exports',
    ], label: 'Exports');
}
```

The key is the permission name and the value is its description, in English. Both the description and the label are [translation keys](#translating-descriptions-and-labels).

Calling `register()` again with the same group adds to it. Without a `label`, the group's label is a headline of its name: `blog_posts` becomes "Blog Posts".

If you pass a list of names instead of pairs, a description is made from each name:

```php
Permissions::register('reports', ['view_reports', 'download_reports']);
// view_reports => "View reports"
```

# The standard permissions of a resource

`resource()` registers the conventional permissions for a model:

```php
Permissions::resource('exports');
```

| Permission | Description |
| --- | --- |
| `view_exports` | View exports |
| `edit_exports` | Edit exports |
| `delete_exports` | Delete exports |
| `force_delete_exports` | Permanently delete exports |

These match the abilities of [`ResourcePolicy`](policies.md). Choose other actions, set the noun used in the descriptions, and set a label:

```php
Permissions::resource(
    'oauth_clients',
    'OAuth2 clients',
    ['view', 'edit', 'edit_others', 'delete', 'delete_others'],
    label: 'OAuth2 clients',
);
// view_oauth_clients => "View OAuth2 clients"
// edit_others_oauth_clients => "Edit all OAuth2 clients"
```

Actions with a built-in description:

| Action | Description |
| --- | --- |
| `view` | View :resource |
| `edit` | Edit :resource |
| `edit_others` | Edit all :resource |
| `delete` | Delete :resource |
| `delete_others` | Delete all :resource |
| `force_delete` | Permanently delete :resource |
| `approve` | Approve :resource |
| `publish` | Publish :resource |
| `import` | Import :resource |
| `export` | Export :resource |

Any other action gets a headline of its name: `archive` becomes "Archive :resource". The noun defaults to the group name with spaces, such as `blog posts` for `blog_posts`.

`resource()` returns the group, so you can add more permissions to it:

```php
Permissions::resource('exports')
    ->add(['download_exports' => 'Download exports']);
```

# Guards

Permissions are created for every guard in `config('permissions.guards')`, or for the app's default guard when that's empty. A group can name its own guards:

```php
Permissions::register('reports', ['view_reports' => 'View reports'])
    ->guards(['web_admin']);
```

See [Multiple guards](../advanced-usage/multi-auth.md).

# Default grants

`grantTo()` gives a role a group's permissions when you sync, if the role exists. Grants only ever add permissions; they never remove what an admin set on the role form.

```php
Permissions::resource('exports')
    ->grantTo('editor')                                     // every permission in the group
    ->grantTo(['auditor', 'viewer'], only: ['view_exports']);
```

The sync skips roles that don't exist and reports them. Packages can't know which roles an app has, so a package should only grant to roles the app defines in [config](roles.md), and say so in its docs.

# Translating descriptions and labels

Descriptions and labels are English strings that are also translation keys. Wherever they're shown, they go through `__()`:

```php
$permission->translated_description; // __('View exports')
$permission->group_label;            // __('Exports')
```

Ship the translations in a JSON file:

```json
// lang/dv.json
{
    "Exports": "އެކްސްޕޯޓްތައް",
    "View exports": "އެކްސްޕޯޓްތައް ބެލުން"
}
```

A package loads that file with `$this->loadJsonTranslationsFrom(__DIR__ . '/../lang')`. An app overrides any line in its own `lang/dv.json`.

# Looking permissions up

```php
Permissions::has('view_exports');           // true
Permissions::description('view_exports');   // "View exports"
Permissions::groupOf('view_exports')->name; // "exports"
Permissions::label('exports');              // "Exports"
Permissions::permissions();                 // ['view_exports' => 'View exports', …]
Permissions::permissions('web_admin');      // only groups on that guard
Permissions::group('exports');              // PermissionGroup, or null
Permissions::groups();                      // every PermissionGroup, by name
Permissions::permissionName('edit', 'exports'); // "edit_exports"
```
