<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::latest()->get();

        return response()->json([
            'success' => true,
            'message' => "Lấy dữ liệu thành công",
            'data' => $brands
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Tạo dữ liệu thành công",
            'data' => $brand
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Lấy chi tiết dữ liệu thành công",
            'data' => $brand
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cập nhật dữ liệu thành công",
            'data' => $brand
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        try {
            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => "Xoá dữ liệu thành công",
                'data' => null
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Xoá dữ liệu thất bại",
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
            'message' => 'Cập nhật trạng thái dữ liệu thành công.',
            'data' => $brand,
        ], 200);
    }
}
