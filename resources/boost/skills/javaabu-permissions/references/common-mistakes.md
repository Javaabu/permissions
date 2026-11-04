# Common mistakes

| Mistake | Instead |
| --- | --- |
| `Permission::create(['name' => 'view_exports'])` in a migration or seeder | `Permissions::resource('exports')` in a provider, then `php artisan permissions:sync` |
| `Gate::policy(Export::class, ExportPolicy::class)` in a package provider | `Permissions::policy(Export::class, ExportPolicy::class)` |
| Registering in `register()` | Register in `boot()` |
| `public function view(User $user, …)` with `App\Models\User` in a package | `public function view(Model $user, …)` |
| `$table->foreignId('user_id')` for an owner | `$table->nullableMorphs('owner')` and `isOwnedBy(Model $user)` |
| `use Spatie\Permission\Traits\HasRoles;` on a user model | `use Javaabu\Permissions\Traits\HasRoles;` |
| `'permissions' => ['view_exports']` on `super_admin` | `'permissions' => '*'` keeps it synced to everything |
| Expecting a pattern role to lose permissions on sync | Only `'*'` roles are synced exactly; others only gain permissions |
| Forgetting the sync after registering | Registering doesn't touch the database; run `php artisan permissions:sync` (and in tests) |
| `@if(auth()->user()->can('a') \|\| auth()->user()->can('b'))` | `@anypermission(['a', 'b'])` |
| Role names in Thaana | Names are ASCII identifiers; put Thaana in the description |
| Expecting `--prune` to remove hand-made permissions | It only prunes groups that are registered |
| `Route::roles()` without a guard in a multi-guard app | `Route::roles(['guard' => 'web_admin'])` |
| `get_setting('default_role')` inside `updateRole()` | `config('permissions.default_role')` or `default_role:` |
