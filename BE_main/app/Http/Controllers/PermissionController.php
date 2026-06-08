<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data' => $permissions
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo dữ liệu thành công',
            'data' => $permission
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết dữ liệu thành công',
            'data' => $permission
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật dữ liệu thành công',
            'data' => $permission
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa dữ liệu thành công',
            'data' => null,
        ], 200);
    }
}
