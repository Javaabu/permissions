# Shipping permissions and policies from a package

Every Javaabu package with controllers ships its permissions and default policy through javaabu/permissions.

```php
namespace Javaabu\Exports;

use Illuminate\Support\ServiceProvider;
use Javaabu\Exports\Models\Export;
use Javaabu\Exports\Policies\ExportPolicy;
use Javaabu\Permissions\Permissions;

class ExportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        Permissions::resource('exports', 'exports', label: 'Exports')
            ->add(['download_exports' => 'Download exports']);

        Permissions::policy(Export::class, ExportPolicy::class);
    }
}
```

- Register in `boot()`, never in `register()`.
- Ship `lang/dv.json` with every description and the label.
- Policies are applied after the app boots. The app wins with `config('permissions.policies')` or `Gate::policy()` in any provider.
- Don't name guards unless a permission only makes sense for one; the app chooses them in `config('permissions.guards')`.
- `->grantTo('editor')` only grants to roles that exist; only use it for roles the app is documented to define.
- Controllers authorize through the policy: `$this->authorize('download', $export)`.
- Routes are a macro the app calls inside its own group, taking `guard` and `middleware` options. Look at `Route::roles()` in `src/Routing/RoleRoutes.php` for the pattern: it stores the guard in the route action (`$request->route()->getAction('permissions_guard')`), defaults middleware to `auth:{guard}`, and reads the controller from config.
- Test with the permissions synced and with two guards (`web` → `User`, `web_admin` → `Admin`).
- Document the permissions you register, and tell apps to run `php artisan permissions:sync` after installing.
