<?php

namespace Javaabu\Permissions\Views\Components;

use Illuminate\Support\Collection;
use Javaabu\Permissions\Models\Role;

class RoleForm extends Component
{
    protected string $view = 'roles._form';

    public string $cancel_redirect_url = '';

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
        string $cancelRedirectUrl = '',
        string $framework = ''
    ) {
        parent::__construct($framework);

        $this->role = $role;
        $this->permissions = $permissions;
        $this->cancel_redirect_url = $cancelRedirectUrl ?: '#';
    }
}
