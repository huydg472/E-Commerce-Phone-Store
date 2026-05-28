<?php

namespace App\Http\Controllers;

use App\Models\ProductSpecification;
use App\Http\Requests\StoreProductSpecificationRequest;
use App\Http\Requests\UpdateProductSpecificationRequest;
use MessageFormatter;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ProductSpecificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $product_specification = ProductSpecification::all();
            return response()->json([
                'status' => true,
                'data' => $product_specification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(ProductSpecification $productSpecification)
    {
        return response()->json([
            'status' => true,
            'data' => $productSpecification
        ]);
    }

    public function store(StoreProductSpecificationRequest $request)
    {
        $data = $request->validated();

        if (!$request->filled('sort_order')) {
            $maxSortOrder = ProductSpecification::where('product_id', $data['product_id'])
                ->max('sort_order');

            $data['sort_order'] = $maxSortOrder === null ? 0 : $maxSortOrder + 1;
        }

        $productSpecification = ProductSpecification::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Thêm thông số sản phẩm thành công',
            'data' => $productSpecification
        ], 201);
    }

    public function update(UpdateProductSpecificationRequest $request, ProductSpecification $productSpecification)
    {
        $data = $request->validated();

        $productSpecification->update($data);

        $productSpecification->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông số sản phẩm thành công',
            'data' => $productSpecification
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductSpecification $productSpecification)
    {
        try {
            $productSpecification->delete();
            return response()->json([
                'message' => 'xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => false,
                'message' => "xóa thất bại"
            ]);
        }
    }
}
