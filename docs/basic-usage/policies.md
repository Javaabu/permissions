---
title: Policies
sidebar_position: 4
---

# Registering a default policy

A package registers the policy for its models through the package, not with `Gate::policy()`, so the app can replace it:

```php
use Javaabu\Permissions\Permissions;

public function boot(): void
{
    Permissions::policy(Export::class, ExportPolicy::class);
}
```

Policies are added to the gate once the app has booted. A package policy is only used when nothing else is set for that model, so the order is:

1. `config('permissions.policies')`, always.
2. A policy the app set with `Gate::policy()`, in any service provider.
3. The package's default.

Subclasses of the model use the same policy, as with `Gate::policy()`.

# Overriding a policy in the app

From config:

```php
// config/permissions.php
'policies' => [
    \Javaabu\Exports\Models\Export::class => \App\Policies\ExportPolicy::class,
],
```

Or from a service provider, as usual:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(\Javaabu\Exports\Models\Export::class, \App\Policies\ExportPolicy::class);
}
```

To change one ability, extend the package's policy:

```php
namespace App\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Exports\Policies\ExportPolicy as BaseExportPolicy;

class ExportPolicy extends BaseExportPolicy
{
    public function delete(Model $user, Model $model): bool
    {
        return parent::delete($user, $model) && $model->created_at->isToday();
    }
}
```

`Permissions::policies()` lists the registered policies with the config overrides applied.

# ResourcePolicy

`Javaabu\Permissions\Policies\ResourcePolicy` maps the standard abilities to the permissions [`Permissions::resource()`](registering-permissions.md#the-standard-permissions-of-a-resource) registers. Set the resource and you're done:

```php
namespace Javaabu\Exports\Policies;

use Javaabu\Permissions\Policies\ResourcePolicy;

class ExportPolicy extends ResourcePolicy
{
    protected string $resource = 'exports';
}
```

| Ability | Allowed with |
| --- | --- |
| `viewAny` | `view_exports` |
| `view` | `view_exports` |
| `create` | `edit_exports` |
| `update` | `edit_exports` |
| `delete` | `delete_exports` |
| `restore` | `delete_exports` or `force_delete_exports` |
| `forceDelete` | `force_delete_exports` |
| `viewTrash` | `delete_exports` or `force_delete_exports` |

Guests are always denied. Every ability takes the user as a `Model`, so it works with any user model.

## Custom abilities

Map other abilities to permissions in `$abilities`. A list means the user needs any of them:

```php
class ExportPolicy extends ResourcePolicy
{
    protected string $resource = 'exports';

    protected array $abilities = [
        'download' => 'download_exports',
        'review' => ['approve_exports', 'publish_exports'],
    ];
}
```

```php
$user->can('download', Export::class);
```

Abilities that aren't defined are denied. For anything more involved, write a method:

```php
public function download(Model $user, Export $export): bool
{
    return $this->allows($user, 'download_exports') && $export->isReady();
}
```

`$this->allows($user, ...$permissions)` is true when the user has any of the permissions. `$this->permission('edit')` returns `edit_exports`.

## Owned resources

With `$owned = true`, `update` and `delete` tell the user's own models apart from other people's:

```php
class OauthClientPolicy extends ResourcePolicy
{
    protected string $resource = 'oauth_clients';

    protected bool $owned = true;
}
```

| Ability | Allowed with |
| --- | --- |
| `update` | `edit_others_oauth_clients`, or `edit_oauth_clients` and owning it |
| `delete` | `delete_others_oauth_clients`, or `delete_oauth_clients` and owning it |
| `view` | `view_oauth_clients`, or being allowed to `update` it |

Register the matching permissions with `Permissions::resource('oauth_clients', actions: ['view', 'edit', 'edit_others', 'delete', 'delete_others'])`.

Ownership calls `$model->isOwnedBy($user)` if the model has it, and is false otherwise. Relate models to users through a morph, so that any user model can own them:

```php
public function isOwnedBy(Model $user): bool
{
    return $this->owner_type === $user->getMorphClass()
        && $this->owner_id == $user->getKey();
}
```

Or override `isOwner(Model $user, Model $model): bool` in the policy.

## Checking one guard

By default, the policy checks permissions through the gate (`$user->can('view_exports')`), so `Gate::before()` callbacks apply. Set `$guard` to check permissions of one guard instead:

```php
protected ?string $guard = 'web_admin';
```

# The role policy

The package registers `Javaabu\Permissions\Policies\RolePolicy` for the role model. It uses `view_roles`, `edit_roles` and `delete_roles`, and the package registers those permissions in the `roles` group. Turn both off with `'register_defaults' => false`, or override the policy like any other.
