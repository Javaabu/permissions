<?php

namespace Javaabu\Permissions\Registry;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Permission\Guard;

class PermissionRegistry
{
    /** Abilities `resource()` creates when none are given */
    public const array DEFAULT_ACTIONS = ['view', 'edit', 'delete', 'force_delete'];

    /** English description templates for the standard actions, used as translation keys */
    public const array ACTION_DESCRIPTIONS = [
        'view' => 'View :resource',
        'edit' => 'Edit :resource',
        'edit_others' => 'Edit all :resource',
        'delete' => 'Delete :resource',
        'delete_others' => 'Delete all :resource',
        'force_delete' => 'Permanently delete :resource',
        'approve' => 'Approve :resource',
        'publish' => 'Publish :resource',
        'import' => 'Import :resource',
        'export' => 'Export :resource',
    ];

    /** @var array<string, PermissionGroup> */
    protected array $groups = [];

    /** @var array<string, RoleDefinition> */
    protected array $roles = [];

    /** @var array<class-string, class-string> */
    protected array $policies = [];

    protected bool $policies_applied = false;

    public function __construct(
        protected Application $app,
    ) {}

    /**
     * Register permissions under a group, such as a model or a package
     *
     * Calling it again with the same group adds to it.
     *
     * @param  array<int|string, string>  $permissions  `name => English description`
     */
    public function register(string $group, array $permissions = [], ?string $label = null): PermissionGroup
    {
        $permission_group = $this->groups[$group] ??= new PermissionGroup($group);

        $permission_group->add($permissions);

        if ($label !== null) {
            $permission_group->label($label);
        }

        return $permission_group;
    }

    /**
     * Register the standard permissions for a resource: `view_exports`, `edit_exports`, …
     *
     * @param  string|null  $noun  the plural noun used in the descriptions, e.g. `exports` in "View exports"
     * @param  array<int, string>  $actions
     */
    public function resource(
        string $group,
        ?string $noun = null,
        array $actions = self::DEFAULT_ACTIONS,
        ?string $label = null,
    ): PermissionGroup {
        $noun ??= str_replace('_', ' ', $group);
        $permissions = [];

        foreach ($actions as $action) {
            $template = self::ACTION_DESCRIPTIONS[$action]
                ?? Str::ucfirst(str_replace('_', ' ', $action)) . ' :resource';

            $permissions[static::permissionName($action, $group)] = str_replace(':resource', $noun, $template);
        }

        return $this->register($group, $permissions, $label);
    }

    /**
     * Define a role the sync creates, and which permissions it gets
     *
     * Roles in `config('permissions.roles')` override roles defined here.
     *
     * @param  string|array<int, string>  $permissions  `*` for every permission, or names and `Str::is()` patterns
     * @param  array<int, string>|string|null  $guards
     */
    public function role(
        string $name,
        ?string $description = null,
        string|array $permissions = [],
        array|string|null $guards = null,
    ): RoleDefinition {
        return $this->roles[$name] = new RoleDefinition(
            $name,
            $description,
            $permissions,
            $guards === null ? null : Arr::wrap($guards),
        );
    }

    /**
     * Register the default policy for a model
     *
     * The app overrides it with `config('permissions.policies')` or `Gate::policy()`.
     *
     * @param  class-string  $model
     * @param  class-string  $policy
     */
    public function policy(string $model, string $policy): static
    {
        $this->policies[$model] = $policy;

        if ($this->policies_applied) {
            $this->applyPolicy($this->app->make(Gate::class), $model, $policy);
        }

        return $this;
    }

    public function group(string $name): ?PermissionGroup
    {
        return $this->groups[$name] ?? null;
    }

    /** @return array<string, PermissionGroup> */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * The registered permissions, as `name => description`
     *
     * @return array<string, string>
     */
    public function permissions(?string $guard = null): array
    {
        $permissions = [];

        foreach ($this->groups as $group) {
            if ($guard !== null && ! in_array($guard, $this->guardsFor($group), true)) {
                continue;
            }

            $permissions = array_merge($permissions, $group->getPermissions());
        }

        return $permissions;
    }

    public function has(string $permission): bool
    {
        return array_key_exists($permission, $this->permissions());
    }

    public function description(string $permission): ?string
    {
        return $this->permissions()[$permission] ?? null;
    }

    public function groupOf(string $permission): ?PermissionGroup
    {
        foreach ($this->groups as $group) {
            if ($group->has($permission)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * The label for a group, untranslated, falling back to a headline of its name
     */
    public function label(string $group): string
    {
        return $this->group($group)?->getLabel() ?? Str::headline($group);
    }

    /**
     * Roles defined in code, overridden by `config('permissions.roles')`
     *
     * @return array<string, RoleDefinition>
     */
    public function roles(): array
    {
        $roles = $this->roles;

        foreach ($this->app['config']->get('permissions.roles', []) as $name => $config) {
            $roles[$name] = RoleDefinition::fromConfig($name, $config ?? []);
        }

        return $roles;
    }

    /**
     * Package policies, overridden by `config('permissions.policies')`
     *
     * @return array<class-string, class-string>
     */
    public function policies(): array
    {
        return array_merge($this->policies, $this->configuredPolicies());
    }

    /**
     * Add the policies to the gate. Config overrides always apply;
     * package defaults only where the app hasn't defined a policy.
     */
    public function applyPolicies(?Gate $gate = null): void
    {
        $gate ??= $this->app->make(Gate::class);

        foreach ($this->policies() as $model => $policy) {
            $this->applyPolicy($gate, $model, $policy);
        }

        $this->policies_applied = true;
    }

    /**
     * The guards permissions are created for when a group doesn't name its own
     *
     * @return array<int, string>
     */
    public function defaultGuards(): array
    {
        $guards = Arr::wrap($this->app['config']->get('permissions.guards'));

        return $guards ?: [$this->app['config']->get('auth.defaults.guard', 'web')];
    }

    /** @return array<int, string> */
    public function guardsFor(PermissionGroup|RoleDefinition $definition): array
    {
        $guards = $definition instanceof PermissionGroup
            ? $definition->getGuards()
            : $definition->guards;

        return $guards ?? $this->defaultGuards();
    }

    /**
     * Every guard the registered permissions and roles use
     *
     * @return array<int, string>
     */
    public function allGuards(): array
    {
        $guards = $this->defaultGuards();

        foreach ([...array_values($this->groups), ...array_values($this->roles())] as $definition) {
            $guards = array_merge($guards, $this->guardsFor($definition));
        }

        return array_values(array_unique($guards));
    }

    /**
     * The guard spatie/laravel-permission uses for a user model
     */
    public function guardFor(Model $user): string
    {
        return Guard::getDefaultName($user);
    }

    /**
     * Whether the user signed in on the guard has any of the permissions
     *
     * @param  string|array<int, string>  $permissions
     */
    public function check(string|array $permissions, ?string $guard = null): bool
    {
        $user = $this->app['auth']->guard($guard)->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'anyPermission')) {
            return $user->anyPermission($permissions, $guard);
        }

        foreach (Arr::wrap($permissions) as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The conventional permission name for an action on a resource: `edit` + `exports` = `edit_exports`
     */
    public static function permissionName(string $action, string $resource): string
    {
        return "{$action}_{$resource}";
    }

    /**
     * Forget everything registered. Meant for tests.
     */
    public function flush(): void
    {
        $this->groups = [];
        $this->roles = [];
        $this->policies = [];
    }

    /** @return array<class-string, class-string> */
    protected function configuredPolicies(): array
    {
        return $this->app['config']->get('permissions.policies', []);
    }

    protected function applyPolicy(Gate $gate, string $model, string $policy): void
    {
        $configured = $this->configuredPolicies();

        if (array_key_exists($model, $configured)) {
            $gate->policy($model, $configured[$model]);

            return;
        }

        if (array_key_exists($model, $gate->policies())) {
            return;
        }

        $gate->policy($model, $policy);
    }
}
