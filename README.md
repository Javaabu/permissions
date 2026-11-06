# Permissions

[![Tests](https://github.com/Javaabu/permissions/actions/workflows/tests.yml/badge.svg?branch=next-gen)](https://github.com/Javaabu/permissions/actions/workflows/tests.yml?query=branch%3Anext-gen)
[![codecov](https://codecov.io/gh/Javaabu/permissions/branch/next-gen/graph/badge.svg)](https://codecov.io/gh/Javaabu/permissions/tree/next-gen)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/permissions.svg)](https://packagist.org/packages/javaabu/permissions)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

Roles, permissions and policies for Laravel apps and packages, built on [spatie/laravel-permission](https://spatie.be/docs/laravel-permission). Apps and packages register permissions, roles and default policies in code, and one command writes them to the database. Permissions are grouped and translatable, policies are overridable, everything works per guard with any number of user models, and roles come with routes, a JSON API and Blade ([javaabu/forms](https://github.com/Javaabu/forms)) or Inertia React ([@javaabu/forms](https://github.com/Javaabu/react-forms)) pages that work in Dhivehi and right to left. Role descriptions can be translated with [javaabu/translatable](https://github.com/Javaabu/translatable).

```php
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\Policies\ResourcePolicy;

// in a service provider's boot()
Permissions::resource('exports', 'exports', label: 'Exports'); // view_exports, edit_exports, …
Permissions::policy(Export::class, ExportPolicy::class);       // the app can override it

class ExportPolicy extends ResourcePolicy
{
    protected string $resource = 'exports';
}
```

```bash
php artisan permissions:sync
```

## Documentation

You'll find the documentation on [https://docs.javaabu.com/permissions](https://docs.javaabu.com/permissions), and in the [`docs`](docs) folder. Upgrading from v2? Read [`docs/upgrading.md`](docs/upgrading.md).

AI coding assistants can start from [`llms.txt`](llms.txt). The package ships a [Laravel Boost](https://github.com/laravel/boost) skill and guideline, which Boost finds by itself. See [AI assistants](docs/ai-assistants.md).

## Quick start

```bash
composer require javaabu/permissions
php artisan migrate
```

```php
use Javaabu\Permissions\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

```bash
php artisan permissions:sync
```

```php
$user->assignRole('super_admin');
$user->can('view_roles'); // true
```

Manage roles from your admin area:

```php
Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::roles(['guard' => 'web_admin']);
});
```

The pages are Blade by default; set `'views' => 'inertia'` in `config/permissions.php` for the React pages. See [Managing roles](docs/basic-usage/managing-roles.md#pages).

## Testing

```bash
composer test
composer lint
pnpm install && pnpm check   # the React pages
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Athfan Khaleel (@athphane)](https://athfan.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
