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

        $guard = RoleRoutes::guard($request);
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
            'roles' => $this->views->stack() === ViewRenderer::INERTIA
                ? RoleResource::collection($roles)
                : $roles,
            'search' => $search,
            'guard' => $guard,
            'routes' => $this->routeNames($request),
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
        $role->forceFill([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'guard_name' => RoleRoutes::guard($request),
        ]);
        $role->save();

        $role->syncPermissionIds($request->validated('permissions', []), $request->user());

        if ($request->expectsJson()) {
            return (new RoleResource($role->load('permissions')))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()
            ->route($this->routeNames($request)['edit'], $role)
            ->with('success', __('Role created'));
    }

    public function show(Request $request, string $role): View|Responsable|JsonResponse
    {
        $role = $this->findRole($request, $role);

        $this->authorizeAbility($request, 'view', $role);

        if ($request->expectsJson()) {
            return (new RoleResource($role->load('permissions')))->response();
        }

        return $this->views->render('roles.show', [
            'role' => $this->views->stack() === ViewRenderer::INERTIA
                ? (new RoleResource($role->load('permissions')))->resolve($request)
                : $role->load('permissions'),
            'permission_groups' => $this->permission_groups->forRole($role),
            'guard' => RoleRoutes::guard($request),
            'routes' => $this->routeNames($request),
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

        $role->forceFill(Arr::only($request->validated(), ['name', 'description']));
        $role->save();

        if ($request->has('permissions') || ! $request->expectsJson()) {
            $role->syncPermissionIds($request->validated('permissions', []), $request->user());
        }

        if ($request->expectsJson()) {
            return (new RoleResource($role->load('permissions')))->response();
        }

        return redirect()
            ->route($this->routeNames($request)['edit'], $role)
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
            ->route($this->routeNames($request)['index'])
            ->with('success', __('Role deleted'));
    }

    protected function form(Request $request, ?Model $role): View|Responsable|JsonResponse
    {
        $guard = RoleRoutes::guard($request);

        $data = [
            'role' => $role ? (new RoleResource($role->load('permissions')))->resolve($request) : null,
            'permission_groups' => $this->permission_groups->forGuard($guard, $role),
            'guard' => $guard,
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        if ($this->views->stack() !== ViewRenderer::INERTIA) {
            $data['role'] = $role;
        }

        return $this->views->render($role ? 'roles.edit' : 'roles.create', [
            ...$data,
            'routes' => $this->routeNames($request),
            'title' => $role ? $role->translated_description : __('New role'),
        ]);
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

    /**
     * Names of the routes, honouring the prefix the app registered them under
     *
     * @return array<string, string>
     */
    protected function routeNames(Request $request): array
    {
        $prefix = RoleRoutes::namePrefix($request);

        return collect(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->mapWithKeys(fn (string $action) => [$action => "{$prefix}.{$action}"])
            ->all();
    }
}
