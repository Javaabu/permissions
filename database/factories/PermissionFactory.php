<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Permissions\Models\Permission;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $prefix = fake()->randomElement(['edit', 'delete', 'approve', 'publish']);
        $suffix = fake()->unique()->word;

        return [
            'model' => $suffix,
            'name' =>  Str::slug("$prefix $suffix", '_'),
            'description' => fake()->sentence,
        ];
    }
}
