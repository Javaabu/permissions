<?php

namespace Javaabu\Permissions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Javaabu\Permissions\Routing\RoleRoutes;
use Javaabu\Permissions\Support\Translations;

class RoleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'translated_description' => $this->translated_description,
            'guard_name' => $this->guard_name,
            'translations' => $this->when(Translations::supportsRole($this->resource), fn () => Translations::values($this->resource)),
            'permissions_count' => $this->whenCounted('permissions'),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'can' => $this->abilities($request),
            'links' => $this->when(RoleRoutes::isRoleRoute($request), fn () => $this->links($request)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * What the current user may do with the role
     *
     * @return array{view: bool, update: bool, delete: bool}
     */
    protected function abilities(Request $request): array
    {
        $user = $request->user();

        return [
            'view' => (bool) $user?->can('view', $this->resource),
            'update' => (bool) $user?->can('update', $this->resource),
            'delete' => (bool) $user?->can('delete', $this->resource),
        ];
    }

    /**
     * URLs of the role's pages, under the route names the app registered them with
     *
     * @return array{show: string, edit: string, update: string, destroy: string}
     */
    protected function links(Request $request): array
    {
        $prefix = RoleRoutes::namePrefix($request);

        return [
            'show' => route("{$prefix}.show", $this->resource),
            'edit' => route("{$prefix}.edit", $this->resource),
            'update' => route("{$prefix}.update", $this->resource),
            'destroy' => route("{$prefix}.destroy", $this->resource),
        ];
    }
}
