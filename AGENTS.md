# permissions: notes for AI coding agents

`javaabu/permissions` wraps spatie/laravel-permission with a registration API for permissions, roles and policies, multi-auth helpers, and role management (routes, controller, JSON API, Blade and Inertia pages). Other Javaabu packages depend on its registration API, so treat `Permissions`, `PermissionGroup`, `ResourcePolicy` and `Route::roles()` as public contracts.

## Layout

| Path | What |
| --- | --- |
| `src/Permissions.php` | Facade over `PermissionRegistry`, plus `sync()`, `ignoreMigrations()`, `permissionName()` |
| `src/Registry` | `PermissionRegistry` (groups, roles, policies, guards), `PermissionGroup`, `RoleDefinition` |
| `src/Sync` | `PermissionSyncer` and `SyncResult`, used by `permissions:sync` and `PermissionsSeeder` |
| `src/Policies` | `ResourcePolicy` base class and `RolePolicy` |
| `src/Traits/HasRoles.php` | Trait for user models (includes spatie's) |
| `src/Models` | `Role` and `Permission`, extending spatie's |
| `src/Routing/RoleRoutes.php`, `src/Http` | `Route::roles()`, `RolesController`, `RoleRequest`, resources |
| `src/Views/ViewRenderer.php` | Renders Blade (`permissions::roles.index`) or Inertia (`permissions::Roles/Index`) |
| `resources/views/roles` | Blade pages, built with javaabu/forms v2 components |
| `resources/js` | Inertia React pages (`Pages/Roles`), components (`@javaabu/permissions`) and the Blade script (`roles.ts`), registered with javaabu/manifest |
| `src/Support/Translations.php`, `src/Models/Translatable` | Optional javaabu/translatable support for role descriptions |
| `config/permissions.php` | Package options; `config/permission.php` is spatie's, preconfigured |
| `database` | Migrations, factories and the seeder (`Javaabu\Permissions\Database\…`) |
| `lang/dv.json` | Dhivehi; English strings are the keys |
| `docs`, `llms.txt` | Docusaurus pages for docs.javaabu.com and the index for AI assistants |
| `resources/boost` | Laravel Boost guideline and skill, shipped in the package |
| `tests` | Testbench tests; two guards (`web` → `User`, `web_admin` → `Admin`). `tests/Translatable` needs javaabu/translatable (suite `Translatable`); `tests/js` are Vitest tests |

## Rules

- Follow Javaabu codestyle ([boost-javaabu-guidelines](https://github.com/Javaabu/boost-javaabu-guidelines)): snake_case variables and properties, camelCase methods, early returns, `#[Test]` and `it_…` test names. Pint enforces formatting.
- Nothing may assume the `web` guard or `App\Models\User`. Type-hint users as `Model`, and test behaviour on both guards.
- Registration must not touch the database; only the syncer writes.
- Package policies are applied after boot and must never replace a policy already on the gate, except from `config('permissions.policies')`.
- No hard dependencies on javaabu/helpers, javaabu/activitylog, javaabu/translatable or Inertia. Optional packages are feature-detected and tested both ways; CI runs the `Package` suite with translatable removed.
- Every page exists in Blade and React with the same props and behaviour. Use logical utilities only (`ms-`, `text-start`); a test rejects `ml-`, `text-left` and the like in Blade views. Component tags of optional packages (`<x-translatable::…>`) go in their own partial.
- Every user-facing string goes through `__()` with an English key, and gets a Dhivehi line in `lang/dv.json`. Use Thaana in tests of user-facing behaviour. List uncertain translations in `docs/advanced-usage/translations.md`.
- Every change needs tests, docs (linked from `llms.txt`), the Boost skill if it changes how the package is used, and a line in `CHANGELOG.md`.
- Work on the `next-gen` branch; don't commit to `main`.

## Commands

```bash
composer install
composer lint      # vendor/bin/pint --test
composer format    # vendor/bin/pint
composer test      # vendor/bin/phpunit
pnpm install       # the React pages' dev tooling
pnpm check         # typecheck and Vitest
```
