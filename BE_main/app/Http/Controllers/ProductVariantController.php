<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    public function index()
    {
        $productVariant = ProductVariant::query()
            ->orderByDesc('id')
            ->get();
        return response()->json(['status' => true, 'message' => 'Lay du lieu thanh cong', 'data' => $productVariant]);
    }

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
            'description' => $request->description,
        ]);

        return response()->json(['status' => true, 'message' => 'Them thanh cong', 'data' => $productVariant]);
    }

    public function show(ProductVariant $productVariant)
    {
        return response()->json(['status' => true, 'message' => 'Lay chi tiet du lieu thanh cong', 'data' => $productVariant]);
    }

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
            'description' => $request->description,
        ]);

        return response()->json(['status' => true, 'message' => 'Cap nhat thanh cong', 'data' => $productVariant]);
    }

    public function destroy(ProductVariant $productVariant)
    {
        try {
            $productVariant->delete();
            return response()->json(['status' => true, 'message' => 'Xoa thanh cong']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Xoa that bai']);
        }
    }

    public function showBySku(string $sku): JsonResponse
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();

        return response()->json([
            'status' => true,
            'message' => 'Lay chi tiet bien the theo SKU thanh cong.',
            'data' => $variant,
        ]);
    }

    public function toggleStatus(ProductVariant $productVariant): JsonResponse
    {
        $productVariant->update([
            'status' => $productVariant->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cap nhat trang thai bien the thanh cong.',
            'data' => $productVariant,
        ]);
    }
}
