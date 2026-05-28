<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productVariant = ProductVariant::all();
        return response()->json([
            'status' => true,
            'data' => $productVariant
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductVariantRequest $request)
    {
        $productVariant = ProductVariant::create([
            'product_id' => $request->product_id,
            'color' => $request->color,
            'storage' => $request->storage,
            'ram' => $request->ram,
            'sku' => $request->sku,
            'import_price' => $request->import_price,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'quantity' => $request->quantity,
            'status' => $request->status,
            'description' => $request->description
        ]);
        return response()->json([
            'message' => 'thêm thành công',
            'data' => $productVariant
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductVariant $productVariant)
    {
        return response()->json([
            'status' => true,
            'data' => $productVariant
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant)
    {
        $productVariant->update([
            'product_id' => $request->product_id,
            'color' => $request->color,
            'storage' => $request->storage,
            'ram' => $request->ram,
            'sku' => $request->sku,
            'import_price' => $request->import_price,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'quantity' => $request->quantity,
            'status' => $request->status,
            'description' => $request->description
        ]);
        return response()->json([
            'message' => 'update thành công',
            'data' => $productVariant
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductVariant $productVariant)
    {
        try {
            $productVariant->delete();
            return response()->json([
                'message' => 'xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'xóa thất bại'
            ]);
        }
    }
    public function showBySku(string $sku): JsonResponse
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();

        return response()->json([
            'status' => true,
            'message' => 'Lấy chi tiết biến thể theo SKU thành công.',
            'data' => $variant
        ]);
    }
    public function toggleStatus(ProductVariant $productVariant): JsonResponse
    {
        $productVariant->update([
            'status' => $productVariant->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái biến thể thành công.',
            'data' => $productVariant,
        ]);
    }
}
