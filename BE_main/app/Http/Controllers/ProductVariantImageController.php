<?php

namespace App\Http\Controllers;

use App\Models\ProductVariantImage;
use App\Http\Requests\StoreProductVariantImageRequest;
use App\Http\Requests\UpdateProductVariantImageRequest;

class ProductVariantImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productVariantImage = ProductVariantImage::all();
        return response()->json([
            'data' => $productVariantImage
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductVariantImageRequest $request)
    {
        $productVariantImage = ProductVariantImage::create([
            'product_variant_id' => $request->product_variant_id,
            'image_url' => $request->image_url,
            'alt_text' => $request->alt_text,
            'short_order' => $request->short_order
        ]);
        return response()->json([
            'message' => 'Thêm thành công',
            'data' => $productVariantImage
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductVariantImage $productVariantImage)
    {
        return response()->json([
            'data' => $productVariantImage
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductVariantImageRequest $request, ProductVariantImage $productVariantImage)
    {
        $productVariantImage->update([
            'product_variant_id' => $request->product_variant_id,
            'image_url' => $request->image_url,
            'alt_text' => $request->alt_text,
            'short_order' => $request->short_order
        ]);
        return response()->json([
            'message' => 'update thành công',
            'data' => $productVariantImage
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductVariantImage $productVariantImage)
    {
        $productVariantImage->delete();
        return response()->json([
            'message' => 'xóa thất bại'
        ]);
    }
}
