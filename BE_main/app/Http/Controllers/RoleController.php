<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with(['permissions:id'])
            ->withCount('permissions')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->permissions()->sync($request->input('permission_ids', []));
        $role->load(['permissions:id']);

        return response()->json([
            'message' => 'thêm thành công',
            'data' => $role,
        ]);
    }

    public function show(Role $role)
    {
        $role->load(['permissions:id']);

        return response()->json([
            'data' => $role,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->input('permission_ids', []));
        }

        $role->load(['permissions:id']);

        return response()->json([
            'message' => 'update thành công',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'xóa thành công',
        ]);
    }
}
