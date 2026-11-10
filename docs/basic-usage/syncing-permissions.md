---
title: Syncing permissions
sidebar_position: 2
---

`permissions:sync` writes the registered permissions and roles to the database:

```bash
php artisan permissions:sync
```

For every guard, it:

1. Creates each registered permission that's missing, and updates its description and group if they changed.
2. Creates the [roles](roles.md) from config and code, and updates their descriptions.
3. Gives each role its permissions. A `*` role is synced to every permission of its guard, so it loses permissions that no longer exist. Any other role only gets permissions added.
4. Applies the [default grants](registering-permissions.md#default-grants) of each group to roles that exist.
5. Clears spatie's permission cache.

Running it twice changes nothing the second time, so put it in your deploy script after `php artisan migrate`.

# Options

```bash
php artisan permissions:sync --guard=web_admin   # only this guard (repeatable)
php artisan permissions:sync --prune             # also delete stale permissions
```

`--prune` deletes permissions whose group is registered but whose name isn't anymore, such as a permission a package removed. Permissions in groups that aren't registered, like ones an app created by hand, are never pruned. Deleting a permission removes it from every role and user.

# From a seeder or code

```php
use Javaabu\Permissions\Database\Seeders\PermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);
    }
}
```

```php
use Javaabu\Permissions\Permissions;

$result = Permissions::sync(guards: ['web_admin'], prune: true);

$result->created_permissions; // ['web_admin:view_exports', …]
$result->updated_permissions;
$result->pruned_permissions;
$result->created_roles;
$result->updated_roles;
$result->skipped_grants;      // grants to roles that don't exist
```

# In tests

Sync in `setUp()` after `RefreshDatabase` has migrated:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->artisan('permissions:sync');
}
```
