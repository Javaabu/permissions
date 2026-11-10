---
title: Translatable role descriptions
sidebar_position: 6.5
---

With [javaabu/translatable](https://github.com/Javaabu/translatable) v3 installed, a role's description can be kept in every language of the app ("Editors" / "ލިޔުންތައް ބަދަލުކުރާ މީހުން"). Without it, nothing changes: a role has one description.

Permission descriptions and group labels aren't stored per role; they're translation keys. Translate those in `lang/dv.json` (see [Dhivehi and translations](translations.md)).

# Setting it up

1. Install and set up javaabu/translatable (its languages table and at least one active language).

    ```bash
    composer require javaabu/translatable:^3.0@dev
    ```

2. Run the migrations. The package's `add_translations_to_roles_table` migration adds two nullable columns, `translations` (JSON) and `lang`, to the roles table. It runs whether or not translatable is installed.

    ```bash
    php artisan migrate
    ```

3. Use the translatable role model:

    ```php
    // config/permission.php
    'models' => [
        'permission' => Javaabu\Permissions\Models\Permission::class,
        'role' => Javaabu\Permissions\Models\Translatable\Role::class,
    ],
    ```

    Or extend it with your own model. `Translatable\Role` extends `Javaabu\Permissions\Models\Role`, keeps its morph class (`getMorphClass()` returns the base role's), and has the same policy.

Roles saved before this read as the default locale (`translatable.default_locale`).

# What changes

| Where | Without translatable | With translatable |
| --- | --- | --- |
| Form fields | `description` | `description_en`, `description_dv`, … one per active language, the default first |
| Validation | `description` required when creating | The role's primary language required when creating, the others optional |
| Blade form | `<x-forms::text name="description">` | `<x-translatable::text name="description">`: a tab per language; Dhivehi with `dir="rtl"`, `lang="dv"` and the Thaana keyboard |
| React form | One `Text` | One `Text` per input in the `translations` prop, with its `dir`, `lang` and `thaana` |
| `RoleResource` | | `translations`: `{ "description_en": "Editors", "description_dv": "ލިޔުންތައް ބަދަލުކުރާ މީހުން" }` |
| `$role->description`, `translated_description` | The stored description | The description in the current locale, falling back to the primary one |

```bash
curl -X POST -H 'Accept: application/json' https://example.test/admin/roles \
    -d name=editor -d description_en=Editors -d 'description_dv=ލިޔުންތައް ބަދަލުކުރާ މީހުން'
```

```php
$role = Role::findByName('editor', 'web_admin');

$role->translate('description', 'dv');                // "ލިޔުންތައް ބަދަލުކުރާ މީހުން"
Javaabu\Translatable\Translatable::values($role);     // ['description_en' => 'Editors', 'description_dv' => '…']
```

The create and edit pages (and their JSON) get `translations`:

```json
{
    "values": { "description_en": "Editors", "description_dv": "ލިޔުންތައް ބަދަލުކުރާ މީހުން" },
    "inputs": {
        "description": [
            { "field": "description", "name": "description_en", "locale": "en", "direction": "ltr", "thaana": false, "primary": true, "label": "Description (English)", "language": { "…": "…" } },
            { "field": "description", "name": "description_dv", "locale": "dv", "direction": "rtl", "thaana": true, "primary": false, "label": "Description (ދިވެހި)", "language": { "…": "…" } }
        ]
    }
}
```

# Checking support in your code

`Javaabu\Permissions\Support\Translations` is safe to call without translatable installed:

| Method | Returns |
| --- | --- |
| `enabled()` | Whether translatable is installed, on, and has a language |
| `supports($model, ?string $field = null)` | Whether the model (instance or class) is translatable |
| `supportsRole(?Model $role = null)` | Whether role descriptions are translated (the configured role class for a new role) |
| `rules(?Model $role, array $rules)` | The rules with `description` expanded per language when supported |
| `values(?Model $role)` | `['description_en' => …]`, or `[]` |
| `form(?Model $role)` | The `translations` prop above, or `null` |

Searching the role list matches the stored description (the primary language) and the name, not the other languages.
