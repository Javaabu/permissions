{{--
    javaabu/permissions guidelines for AI code assistants

    Maintained by: Javaabu (https://javaabu.com)
--}}

## javaabu/permissions

- This project manages roles, permissions and policies with `javaabu/permissions`, built on spatie/laravel-permission. Register permissions in code with `Javaabu\Permissions\Permissions::register()` or `::resource()`, then run `php artisan permissions:sync`. Never create permissions in migrations or seeders.
- Always activate the `javaabu-permissions` skill when adding or checking permissions, writing a policy, defining roles, registering role routes, or working with multiple guards.
- The role pages render as Blade (javaabu/forms) or Inertia React (`permissions::Roles/*`, through javaabu/manifest), set by `config('permissions.views')`. Override them with `vendor:publish --tag=permissions-views` or `php artisan manifest:override permissions <file>`; don't copy them.
