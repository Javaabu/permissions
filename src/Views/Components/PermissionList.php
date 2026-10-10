<?php

namespace Javaabu\Permissions\Views\Components;

use Illuminate\Support\Collection;
use Javaabu\Permissions\Models\Role;

class PermissionList extends Component
{
    protected string $view = 'roles.form._permissions';

    public ?Role $role;

    public Collection $permissions;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        Collection $permissions,
        ?Role $role = null,
        string $framework = ''
    ) {
        parent::__construct($framework);

        $this->role = $role;
        $this->permissions = $permissions;
    }
}
