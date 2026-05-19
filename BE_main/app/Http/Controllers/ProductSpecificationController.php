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
     * Store a newly created resource in storage.
     */
    public function store(StoreProductSpecificationRequest $request)
    {
        $productSpecification = ProductSpecification::create([
            'product_id' => $request->poduct_id,
            'spec_name' => $request->spec_name,
            'spec_value' => $request->spec_value,
            'short_order' => $request->short_order,
        ]);
        return response()->json([
            'message' => 'thêm thành công',
            'data' => $productSpecification
        ]);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductSpecificationRequest $request, ProductSpecification $productSpecification)
    {
        $productSpecification->update([
            'product_id' => $request->poduct_id,
            'spec_name' => $request->spec_name,
            'spec_value' => $request->spec_value,
            'short_order' => $request->short_order
        ]);
        return response()->json([
            'message' => 'update thành công',
            'data' => $productSpecification,
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
