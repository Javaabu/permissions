<?php

namespace Javaabu\Permissions\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Javaabu\Permissions\Routing\RoleRoutes;
use Javaabu\Permissions\Support\Translations;
use Spatie\Permission\PermissionRegistrar;

class RoleRequest extends FormRequest
{
    /**
     * Authorization is done by the controller through the role policy
     */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $guard = RoleRoutes::guard($this);
        $tables = config('permission.table_names');
        $role = $this->role();
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        $rules = [
            'name' => [
                $required,
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique($tables['roles'], 'name')
                    ->where('guard_name', $guard)
                    ->ignore($role?->getKey()),
            ],
            'description' => [$required, 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'integer',
                Rule::exists($tables['permissions'], 'id')->where('guard_name', $guard),
            ],
        ];

        // description_en, description_dv, … when javaabu/translatable translates descriptions
        return Translations::rules($role, $rules);
    }

    /**
     * The role being updated, if any, from the route's guard
     */
    public function role(): ?Model
    {
        $role = $this->route('role');

        if ($role === null || $role instanceof Model) {
            return $role;
        }

        return app(PermissionRegistrar::class)->getRoleClass()::query()
            ->where('guard_name', RoleRoutes::guard($this))
            ->find($role);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [
            'name' => __('name'),
            'description' => __('description'),
            'permissions' => __('permissions'),
            'permissions.*' => __('permission'),
        ];

        foreach (Translations::form($this->role())['inputs']['description'] ?? [] as $input) {
            $attributes[$input['name']] = $input['label'];
        }

        return $attributes;
    }
}
