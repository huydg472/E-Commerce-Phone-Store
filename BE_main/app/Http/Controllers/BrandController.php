<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lay du lieu thanh cong',
            'data' => $brands,
        ], 200);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tao du lieu thanh cong',
            'data' => $brand,
        ], 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lay chi tiet du lieu thanh cong',
            'data' => $brand,
        ], 200);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat du lieu thanh cong',
            'data' => $brand,
        ], 200);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        try {
            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xoa du lieu thanh cong',
                'data' => null,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xoa du lieu that bai',
            ], 409);
        }
    }

    public function toggleStatus(Brand $brand): JsonResponse
    {
        $brand->update([
            'status' => $brand->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat trang thai du lieu thanh cong.',
            'data' => $brand,
        ], 200);
    }
}
