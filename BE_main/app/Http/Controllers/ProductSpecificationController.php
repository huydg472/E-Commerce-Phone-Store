<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductSpecificationRequest;
use App\Http\Requests\UpdateProductSpecificationRequest;
use App\Models\ProductSpecification;

class ProductSpecificationController extends Controller
{
    public function index()
    {
        try {
            $productSpecification = ProductSpecification::query()
                ->with(['product'])
                ->orderByDesc('id')
                ->get();
            return response()->json(['status' => true, 'message' => 'Lay du lieu thanh cong', 'data' => $productSpecification]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function show(ProductSpecification $productSpecification)
    {
        $productSpecification->load(['product']);

        return response()->json(['status' => true, 'message' => 'Lay chi tiet du lieu thanh cong', 'data' => $productSpecification]);
    }

    public function store(StoreProductSpecificationRequest $request)
    {
        $data = $request->validated();
        if (!$request->filled('sort_order')) {
            $maxSortOrder = ProductSpecification::where('product_id', $data['product_id'])->max('sort_order');
            $data['sort_order'] = $maxSortOrder === null ? 0 : $maxSortOrder + 1;
        }

        $productSpecification = ProductSpecification::create($data);
        $productSpecification->load(['product']);
        return response()->json(['status' => true, 'message' => 'Them thong so san pham thanh cong', 'data' => $productSpecification], 201);
    }

    public function update(UpdateProductSpecificationRequest $request, ProductSpecification $productSpecification)
    {
        $productSpecification->update($request->validated());
        $productSpecification->refresh();
        $productSpecification->load(['product']);
        return response()->json(['status' => true, 'message' => 'Cap nhat thong so san pham thanh cong', 'data' => $productSpecification]);
    }

    public function destroy(ProductSpecification $productSpecification)
    {
        try {
            $productSpecification->delete();
            return response()->json(['status' => true, 'message' => 'Xoa thanh cong']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Xoa that bai']);
        }
    }
}
