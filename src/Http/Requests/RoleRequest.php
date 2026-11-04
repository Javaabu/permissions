<?php

namespace Javaabu\Permissions\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Javaabu\Permissions\Routing\RoleRoutes;

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
        $role = $this->route('role');
        $role_id = $role instanceof Model ? $role->getKey() : $role;

        return [
            'name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique($tables['roles'], 'name')
                    ->where('guard_name', $guard)
                    ->ignore($role_id),
            ],
            'description' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'integer',
                Rule::exists($tables['permissions'], 'id')->where('guard_name', $guard),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('name'),
            'description' => __('description'),
            'permissions' => __('permissions'),
            'permissions.*' => __('permission'),
        ];
    }
}
