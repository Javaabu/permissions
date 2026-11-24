---
title: For package authors
sidebar_position: 1
---

Every Javaabu package with controllers ships its permissions and policies through this package, so that apps manage them in one place and can override them. Here is a complete example for a package with an `Export` model.

# composer.json

```json
{
    "require": {
        "javaabu/permissions": "^3.0"
    }
}
```

# The service provider

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

# The policy

```php
namespace Javaabu\Exports\Policies;

use Javaabu\Permissions\Policies\ResourcePolicy;

class ExportPolicy extends ResourcePolicy
{
    protected string $resource = 'exports';

    protected array $abilities = [
        'download' => 'download_exports',
    ];
}
```

# Translations

```json
// lang/dv.json
{
    "Exports": "އެކްސްޕޯޓްތައް",
    "View exports": "އެކްސްޕޯޓްތައް ބެލުން",
    "Edit exports": "އެކްސްޕޯޓްތައް ބަދަލުކުރުން",
    "Delete exports": "އެކްސްޕޯޓްތައް ފުހެލުން",
    "Permanently delete exports": "އެކްސްޕޯޓްތައް ދާއިމީކޮށް ފުހެލުން",
    "Download exports": "އެކްސްޕޯޓްތައް ޑައުންލޯޑުކުރުން"
}
```

# The controller

Authorize with the policy as usual. Don't check a guard name or `App\Models\User`:

```php
public function index(Request $request)
{
    $this->authorize('viewAny', Export::class);

    // …
}

public function download(Request $request, Export $export)
{
    $this->authorize('download', $export);

    // …
}
```

# Tests

Test with the permissions synced, and with two user models on two guards:

```php
protected function setUp(): void
{
    parent::setUp();

    config()->set('permissions.guards', ['web', 'web_admin']);

    $this->artisan('permissions:sync');
}

#[Test]
public function it_needs_the_download_permission(): void
{
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'web_admin')
        ->get(route('exports.download', $export))
        ->assertForbidden();

    $admin->givePermissionTo('download_exports');

    $this->actingAs($admin, 'web_admin')
        ->get(route('exports.download', $export))
        ->assertOk();
}
```

# What to document in your package

- The permissions it registers and what each one allows.
- That apps run `php artisan permissions:sync` after installing or updating it.
- How to override its policy: `config('permissions.policies')` or `Gate::policy()`.

# Rules

- Register in `boot()`, not `register()`.
- Use `Permissions::policy()`, never `Gate::policy()`, for your default policies, or the app can't override them.
- Don't assume roles exist. Only use `grantTo()` for roles the app is documented to define.
- Don't create permissions with `Permission::create()` in migrations or seeders. Register them and let the sync create them.
- Don't hard-code guards. Leave them to `config('permissions.guards')` unless the permission only makes sense for one guard.
