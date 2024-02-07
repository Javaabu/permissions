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
        $prefix = $this->faker->randomElement(['edit', 'delete', 'approve', 'publish']);
        $suffix = $this->faker->word;

        return [
            'model' => $suffix,
            'name' => Str::slug("$prefix $suffix", '_'),
            'description' => $this->faker->sentence,
        ];
    }
}
