<?php

use Javaabu\Permissions\Http\Controllers\RolesController;

return [

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    |
    | The guards registered permissions and roles are created for, unless a
    | group or role names its own. Leave empty to use the app's default guard.
    | Example: ['web_admin', 'api']
    |
    */

    'guards' => [],

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | Roles `php artisan permissions:sync` creates for each guard. `permissions`
    | is `*` for every permission of the guard (kept in sync), or a list of
    | names and patterns such as `view_*`, which are only ever added.
    | These override roles defined in code with Permissions::role().
    |
    */

    'roles' => [
        'super_admin' => [
            'description' => 'Super Admin',
            'permissions' => '*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default role
    |--------------------------------------------------------------------------
    |
    | The role HasRoles::updateRole() gives a new user when the person
    | creating them isn't allowed to choose a role.
    |
    */

    'default_role' => null,

    /*
    |--------------------------------------------------------------------------
    | Policy overrides
    |--------------------------------------------------------------------------
    |
    | Replace the policy a package registered for a model with your own.
    | Example: [Javaabu\Exports\Models\Export::class => App\Policies\ExportPolicy::class]
    |
    */

    'policies' => [],

    /*
    |--------------------------------------------------------------------------
    | Activity log
    |--------------------------------------------------------------------------
    |
    | Log role and permission changes (`role_updated`, `permissions_updated`)
    | when spatie/laravel-activitylog is installed.
    |
    */

    'log_activity' => true,

    /*
    |--------------------------------------------------------------------------
    | Default permissions
    |--------------------------------------------------------------------------
    |
    | Register this package's own permissions (view_roles, edit_roles,
    | delete_roles) and its policy for the role model.
    |
    */

    'register_defaults' => true,

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Which stack the role pages render with: `blade` (javaabu/forms) or
    | `inertia` (@javaabu/forms React pages shipped through javaabu/manifest).
    |
    */

    'views' => 'blade',

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | The controllers Route::roles() uses. Extend the package's controller to
    | change its behaviour.
    |
    */

    'controllers' => [
        'roles' => RolesController::class,
    ],

];
