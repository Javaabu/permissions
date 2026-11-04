<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Javaabu\Permissions\Models\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => Str::slug(fake()->unique()->word(), '_'),
            'description' => fake()->sentence,
        ];
    }
}
