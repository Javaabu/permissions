<?php

namespace Javaabu\Permissions\Registry;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RoleDefinition
{
    /**
     * @param  string|array<int, string>  $permissions  `*` for every permission, or names and `Str::is()` patterns
     * @param  array<int, string>|null  $guards
     */
    public function __construct(
        public readonly string $name,
        public ?string $description = null,
        public string|array $permissions = [],
        public ?array $guards = null,
    ) {}

    /**
     * @param  array{
     *     description?: string|null,
     *     permissions?: string|array<int, string>,
     *     guards?: array<int, string>|string|null
     * }  $config
     */
    public static function fromConfig(string $name, array $config): static
    {
        return new static(
            $name,
            $config['description'] ?? null,
            $config['permissions'] ?? [],
            isset($config['guards']) ? Arr::wrap($config['guards']) : null,
        );
    }

    public function getDescription(): string
    {
        return $this->description ?? Str::headline($this->name);
    }

    public function grantsEverything(): bool
    {
        return $this->permissions === '*' || $this->permissions === ['*'];
    }

    /**
     * Pick the permissions this role should get from the given names
     *
     * @param  array<int, string>  $available
     * @return array<int, string>
     */
    public function resolvePermissions(array $available): array
    {
        if ($this->grantsEverything()) {
            return array_values($available);
        }

        $patterns = Arr::wrap($this->permissions);

        return array_values(array_filter(
            $available,
            fn (string $permission) => Str::is($patterns, $permission),
        ));
    }
}
