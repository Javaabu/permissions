<?php

namespace Javaabu\Permissions\Tests\Fixtures;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Permissions\Http\Controllers\RolesController;

class CustomRolesController extends RolesController
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['custom' => true]);
    }
}
