---
title: Requirements
sidebar_position: 1.1
---

- PHP 8.4 or higher
- Laravel 12 or 13
- [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) 7.2 or higher, [javaabu/forms](https://github.com/Javaabu/forms) 2 and [javaabu/manifest](https://github.com/Javaabu/manifest) 1 (installed with the package)

Optional:

| Package | For |
| --- | --- |
| [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) ^4.10 | [Logging role and permission changes](advanced-usage/activity-log.md) |
| [inertiajs/inertia-laravel](https://inertiajs.com) ^2.0 | Rendering the [role pages](basic-usage/managing-roles.md) with Inertia React (with `@javaabu/forms`, `@javaabu/onu`, React 18.3 or 19 and `@inertiajs/react` 2 or 3 in your app) |
| [javaabu/translatable](https://github.com/Javaabu/translatable) ^3.0 | [Translating role descriptions](advanced-usage/translatable-roles.md) |

Until javaabu/forms v2, javaabu/manifest and javaabu/translatable v3 are on Packagist, add their repositories to your `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/Javaabu/forms" },
    { "type": "vcs", "url": "https://github.com/Javaabu/manifest" },
    { "type": "vcs", "url": "https://github.com/n4ff4h/translatable" }
]
```
