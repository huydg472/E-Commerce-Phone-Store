<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo dữ liệu thành công',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết dữ liệu thành công',
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật dữ liệu thành công',
            'data' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xoá dữ liệu thành công',
                'data' => null
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xoá dữ liệu thất bại',
            ], 409);
        }
    }

    public function toggleStatus(Category $category): JsonResponse
    {
        $category->update([
            'status' => $category->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái dữ liệu thành công.',
            'data' => $category,
        ], 200);
    }
}
