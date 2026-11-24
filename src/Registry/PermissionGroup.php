<?php

namespace Javaabu\Permissions\Registry;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PermissionGroup
{
    /** @var array<string, string> */
    protected array $permissions = [];

    protected ?string $label = null;

    /** @var array<int, string>|null */
    protected ?array $guards = null;

    /** @var array<string, array<int, string>|null> */
    protected array $grants = [];

    public function __construct(
        public readonly string $name,
    ) {}

    /**
     * Add permissions to the group, as `name => description` pairs or a list of names
     *
     * @param  array<int|string, string>  $permissions
     */
    public function add(array $permissions): static
    {
        foreach ($permissions as $name => $description) {
            if (is_int($name)) {
                $name = $description;
                $description = Str::ucfirst(str_replace('_', ' ', $description));
            }

            $this->permissions[$name] = $description;
        }

        return $this;
    }

    /**
     * Set the label shown for the group, an English string used as a translation key
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Limit the group to the given guards instead of the configured default guards
     *
     * @param  array<int, string>|string  $guards
     */
    public function guards(array|string $guards): static
    {
        $this->guards = Arr::wrap($guards);

        return $this;
    }

    /**
     * Give roles, if they exist, the permissions of this group when syncing
     *
     * @param  array<int, string>|string  $roles
     * @param  array<int, string>|null  $only  permission names; all the group's permissions when null
     */
    public function grantTo(array|string $roles, ?array $only = null): static
    {
        foreach (Arr::wrap($roles) as $role) {
            $this->grants[$role] = $only;
        }

        return $this;
    }

    /** @return array<string, string> */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::headline($this->name);
    }

    /** @return array<int, string>|null */
    public function getGuards(): ?array
    {
        return $this->guards;
    }

    /** @return array<string, array<int, string>> */
    public function getGrants(): array
    {
        $grants = [];

        foreach ($this->grants as $role => $only) {
            $grants[$role] = $only === null
                ? array_keys($this->permissions)
                : array_values(array_intersect($only, array_keys($this->permissions)));
        }

        return $grants;
    }

    public function has(string $permission): bool
    {
        return array_key_exists($permission, $this->permissions);
    }
}
