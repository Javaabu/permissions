<?php

namespace Javaabu\Permissions\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Javaabu\Permissions\Http\Requests\RoleRequest;
use Javaabu\Permissions\Http\Resources\RoleResource;
use Javaabu\Permissions\Routing\RoleRoutes;
use Javaabu\Permissions\Support\PermissionGroups;
use Javaabu\Permissions\Support\Translations;
use Javaabu\Permissions\Views\ViewRenderer;
use Spatie\Permission\PermissionRegistrar;

/**
 * Manage the roles of the guard the routes were registered for
 *
 * Every action answers JSON when the request expects it, and a Blade or Inertia page otherwise.
 * Override it with `config('permissions.controllers.roles')`, the `controller` option of
 * `Route::roles()`, or by binding a subclass in the container.
 */
class RolesController extends Controller
{
    public function __construct(
        protected ViewRenderer $views,
        protected PermissionGroups $permission_groups,
    ) {}

    public function index(Request $request): View|Responsable|JsonResponse
    {
        $this->authorizeAbility($request, 'viewAny', $this->roleClass());

        $search = $request->string('search')->trim()->toString() ?: null;
        $per_page = min(max($request->integer('per_page', 20), 1), 100);

        $roles = $this->query($request)
            ->search($search)
            ->withCount('permissions')
            ->orderBy('name')
            ->paginate($per_page)
            ->withQueryString();

        if ($request->expectsJson()) {
            return RoleResource::collection($roles)->response();
        }

        return $this->views->render('roles.index', [
            ...$this->meta($request),
            'roles' => $this->views->isInertia()
                ? RoleResource::collection($roles)
                : $roles,
            'search' => $search,
            'title' => $search
                ? __('Roles matching \':search\'', ['search' => $search])
                : __('Roles'),
        ]);
    }

    public function create(Request $request): View|Responsable|JsonResponse
    {
        $this->authorizeAbility($request, 'create', $this->roleClass());

        return $this->form($request, null);
    }

    public function store(RoleRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorizeAbility($request, 'create', $this->roleClass());

        $role_class = $this->roleClass();

        /** @var Model $role */
        $role = new $role_class;
        $role->fill($this->attributes($request));
        $role->forceFill(['guard_name' => RoleRoutes::guard($request)]);
        $role->save();

        $role->syncPermissionIds($request->validated('permissions', []), $request->user());

        if ($request->expectsJson()) {
            return (new RoleResource($role->load('permissions')))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()
            ->route($this->routeName($request, 'edit'), $role)
            ->with('success', __('Role created'));
    }

    public function show(Request $request, string $role): View|Responsable|JsonResponse
    {
        $role = $this->findRole($request, $role);

        $this->authorizeAbility($request, 'view', $role);

        $role->load('permissions');

        if ($request->expectsJson()) {
            return (new RoleResource($role))->response();
        }

        return $this->views->render('roles.show', [
            ...$this->meta($request, $role),
            'role' => $this->views->isInertia()
                ? (new RoleResource($role))->resolve($request)
                : $role,
            'permission_groups' => $this->permission_groups->forRole($role),
            'title' => $role->translated_description,
        ]);
    }

    public function edit(Request $request, string $role): View|Responsable|JsonResponse
    {
        $role = $this->findRole($request, $role);

        $this->authorizeAbility($request, 'update', $role);

        return $this->form($request, $role);
    }

    public function update(RoleRequest $request, string $role): RedirectResponse|JsonResponse
    {
        $role = $this->findRole($request, $role);

        $this->authorizeAbility($request, 'update', $role);

        $role->fill($this->attributes($request));
        $role->save();

        if ($request->has('permissions') || ! $request->expectsJson()) {
            $role->syncPermissionIds($request->validated('permissions', []), $request->user());
        }

        if ($request->expectsJson()) {
            return (new RoleResource($role->load('permissions')))->response();
        }

        return redirect()
            ->route($this->routeName($request, 'edit'), $role)
            ->with('success', __('Role updated'));
    }

    public function destroy(Request $request, string $role): RedirectResponse|JsonResponse
    {
        $role = $this->findRole($request, $role);

        $this->authorizeAbility($request, 'delete', $role);

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($request->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route($this->routeName($request, 'index'))
            ->with('success', __('Role deleted'));
    }

    /**
     * The create and edit pages, or their data as JSON
     */
    protected function form(Request $request, ?Model $role): View|Responsable|JsonResponse
    {
        $role?->load('permissions');

        $data = [
            'role' => $role ? (new RoleResource($role))->resolve($request) : null,
            'permission_groups' => $this->permission_groups->forGuard(RoleRoutes::guard($request), $role),
            'guard' => RoleRoutes::guard($request),
            'translations' => Translations::form($role),
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        if (! $this->views->isInertia()) {
            $data['role'] = $role;
        }

        return $this->views->render($role ? 'roles.edit' : 'roles.create', [
            ...$this->meta($request, $role),
            ...$data,
            'title' => $role ? $role->translated_description : __('New role'),
        ]);
    }

    /**
     * The role's own attributes from the request: name and description, or a description per
     * locale when descriptions are translated
     *
     * @return array<string, mixed>
     */
    protected function attributes(RoleRequest $request): array
    {
        return Arr::except($request->validated(), ['permissions']);
    }

    /**
     * What every page gets: the guard, route names, URLs, what the user may do and, for the
     * Inertia pages, their translated text
     *
     * @return array<string, mixed>
     */
    protected function meta(Request $request, ?Model $role = null): array
    {
        $user = $request->user();

        return [
            'guard' => RoleRoutes::guard($request),
            'routes' => $this->routeNames($request),
            'urls' => array_filter([
                'index' => route($this->routeName($request, 'index')),
                'create' => route($this->routeName($request, 'create')),
                'store' => route($this->routeName($request, 'store')),
                'show' => $role ? route($this->routeName($request, 'show'), $role) : null,
                'edit' => $role ? route($this->routeName($request, 'edit'), $role) : null,
                'update' => $role ? route($this->routeName($request, 'update'), $role) : null,
                'destroy' => $role ? route($this->routeName($request, 'destroy'), $role) : null,
            ]),
            'can' => [
                'create' => (bool) $user?->can('create', $this->roleClass()),
                'update' => $role ? (bool) $user?->can('update', $role) : false,
                'delete' => $role ? (bool) $user?->can('delete', $role) : false,
            ],
            'strings' => $this->views->isInertia() ? $this->strings() : null,
        ];
    }

    /**
     * The translated text of the Inertia pages (Blade views use __() directly)
     *
     * @return array<string, string>
     */
    protected function strings(): array
    {
        return [
            'roles' => __('Roles'),
            'new_role' => __('New role'),
            'search' => __('Search roles'),
            'name' => __('Name'),
            'name_help' => __('Letters, numbers, dashes and underscores, e.g. content_editor'),
            'description' => __('Description'),
            'permissions' => __('Permissions'),
            'permissions_count' => __(':count permissions'),
            'actions' => __('Actions'),
            'view' => __('View'),
            'edit' => __('Edit'),
            'delete' => __('Delete'),
            'save' => __('Save'),
            'create' => __('Create role'),
            'back' => __('Back to roles'),
            'confirm_delete' => __('Delete this role? Users with it lose its permissions.'),
            'no_roles' => __('No roles yet'),
            'no_roles_message' => __('Roles you create will appear here.'),
            'no_results' => __('No roles match your search.'),
            'select_all' => __('Select all'),
            'select_none' => __('Select none'),
            'no_permissions' => __('This role has no permissions.'),
            'details' => __('Details'),
            'guard' => __('Guard'),
        ];
    }

    /**
     * Roles of the guard the route manages
     */
    protected function query(Request $request): Builder
    {
        return $this->roleClass()::query()->where('guard_name', RoleRoutes::guard($request));
    }

    protected function findRole(Request $request, string $role): Model
    {
        return $this->query($request)->findOrFail($role);
    }

    protected function authorizeAbility(Request $request, string $ability, mixed $arguments): void
    {
        $user = $request->user();

        abort_unless($user && $user->can($ability, $arguments), 403);
    }

    /** @return class-string<Model> */
    protected function roleClass(): string
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    protected function routeName(Request $request, string $action): string
    {
        return RoleRoutes::namePrefix($request) . ".{$action}";
    }

    /**
     * Names of the routes, honouring the prefix the app registered them under
     *
     * @return array<string, string>
     */
    protected function routeNames(Request $request): array
    {
        return collect(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->mapWithKeys(fn (string $action) => [$action => $this->routeName($request, $action)])
            ->all();
    }
}
