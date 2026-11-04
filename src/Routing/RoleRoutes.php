<?php

namespace Javaabu\Permissions\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Javaabu\Permissions\Http\Controllers\RolesController;
use Javaabu\Permissions\Registry\PermissionRegistry;

/**
 * Registers the role management routes for `Route::roles()`
 */
class RoleRoutes
{
    /** The route action key that holds the guard the routes manage roles for */
    public const string GUARD_ACTION = 'permissions_guard';

    /**
     * @param  array{
     *     guard?: string|null,
     *     prefix?: string,
     *     name?: string,
     *     middleware?: string|array<int, string>|null,
     *     controller?: class-string|null
     * }  $options
     */
    public static function register(Router $router, array $options = []): void
    {
        $guard = $options['guard'] ?? app(PermissionRegistry::class)->defaultGuards()[0];
        $prefix = $options['prefix'] ?? 'roles';
        $name = rtrim($options['name'] ?? 'roles', '.');
        $middleware = $options['middleware'] ?? ["auth:{$guard}"];
        $controller = $options['controller'] ?? config('permissions.controllers.roles', RolesController::class);

        $router->group([
            'prefix' => $prefix,
            'as' => "{$name}.",
            'middleware' => $middleware,
            self::GUARD_ACTION => $guard,
        ], function (Router $router) use ($controller) {
            $router->get('/', [$controller, 'index'])->name('index');
            $router->get('/create', [$controller, 'create'])->name('create');
            $router->post('/', [$controller, 'store'])->name('store');
            $router->get('/{role}', [$controller, 'show'])->name('show');
            $router->get('/{role}/edit', [$controller, 'edit'])->name('edit');
            $router->match(['put', 'patch'], '/{role}', [$controller, 'update'])->name('update');
            $router->delete('/{role}', [$controller, 'destroy'])->name('destroy');
        });
    }

    /**
     * The guard the current route manages roles for
     */
    public static function guard(Request $request): string
    {
        return $request->route()?->getAction(self::GUARD_ACTION)
            ?? app(PermissionRegistry::class)->defaultGuards()[0];
    }

    /**
     * The route name prefix of the current route, e.g. `admin.roles`
     */
    public static function namePrefix(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return str_contains($name, '.')
            ? substr($name, 0, strrpos($name, '.'))
            : 'roles';
    }
}
