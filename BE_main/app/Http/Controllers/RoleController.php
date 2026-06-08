<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $role = Role::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $role
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());

        return response()->json([
            'message' => 'thÃªm thÃ nh cÃ´ng',
            'data' => $role
        ]);
    }

    public function show(Role $role)
    {
        return response()->json([
            'data' => $role
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        return response()->json([
            'message' => 'update thÃ nh cÃ´ng',
            'data' => $role
        ]);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'xÃ³a thÃ nh cÃ´ng',
        ]);
    }
}
