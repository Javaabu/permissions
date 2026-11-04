<?php

namespace Javaabu\Permissions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'translated_description' => $this->translated_description,
            'group' => $this->model,
            'guard_name' => $this->guard_name,
        ];
    }
}
