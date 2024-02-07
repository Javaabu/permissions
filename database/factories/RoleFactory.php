<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Permissions\Models\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name'        => Str::slug($this->faker->word, '_'),
            'description' => $this->faker->sentence,
        ];
    }
}
