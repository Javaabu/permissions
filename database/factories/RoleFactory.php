<?php

namespace Javaabu\Permissions\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Javaabu\Permissions\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * The configured role class, so the factory makes translatable or app roles too
     */
    public function modelName(): string
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    public function definition(): array
    {
        return [
            'name' => Str::slug(fake()->unique()->word(), '_'),
            'description' => fake()->sentence,
        ];
    }
}
