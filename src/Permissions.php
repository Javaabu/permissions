<?php

namespace Javaabu\Permissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Javaabu\Permissions\Registry\PermissionGroup;
use Javaabu\Permissions\Registry\PermissionRegistry;
use Javaabu\Permissions\Registry\RoleDefinition;
use Javaabu\Permissions\Sync\PermissionSyncer;
use Javaabu\Permissions\Sync\SyncResult;

/**
 * @method static PermissionGroup register(string $group, array $permissions = [], ?string $label = null)
 * @method static PermissionGroup resource(string $group, ?string $noun = null, array $actions = PermissionRegistry::DEFAULT_ACTIONS, ?string $label = null)
 * @method static RoleDefinition role(string $name, ?string $description = null, string|array $permissions = [], array|string|null $guards = null)
 * @method static PermissionRegistry policy(string $model, string $policy)
 * @method static PermissionGroup|null group(string $name)
 * @method static array<string, PermissionGroup> groups()
 * @method static array<string, string> permissions(?string $guard = null)
 * @method static bool has(string $permission)
 * @method static string|null description(string $permission)
 * @method static PermissionGroup|null groupOf(string $permission)
 * @method static string label(string $group)
 * @method static array<string, RoleDefinition> roles()
 * @method static array<class-string, class-string> policies()
 * @method static void applyPolicies(?\Illuminate\Contracts\Auth\Access\Gate $gate = null)
 * @method static array<int, string> defaultGuards()
 * @method static array<int, string> guardsFor(PermissionGroup|RoleDefinition $definition)
 * @method static array<int, string> allGuards()
 * @method static string guardFor(Model $user)
 * @method static bool check(string|array $permissions, ?string $guard = null)
 * @method static void flush()
 *
 * @see PermissionRegistry
 */
class Permissions extends Facade
{
    public static bool $runs_migrations = true;

    /**
     * Stop the package from loading its migrations, e.g. after publishing them
     */
    public static function ignoreMigrations(): void
    {
        static::$runs_migrations = false;
    }

    /**
     * Create or update the registered permissions and roles in the database
     *
     * @param  array<int, string>|null  $guards
     */
    public static function sync(?array $guards = null, bool $prune = false): SyncResult
    {
        return static::$app->make(PermissionSyncer::class)->sync($guards, $prune);
    }

    public static function permissionName(string $action, string $resource): string
    {
        return PermissionRegistry::permissionName($action, $resource);
    }

    protected static function getFacadeAccessor(): string
    {
        return PermissionRegistry::class;
    }
}
