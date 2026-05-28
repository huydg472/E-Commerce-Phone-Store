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


    /**
     * Display the specified resource.
     */
    public function show(ProductVariantImage $productVariantImage)
    {
        return response()->json([
            'data' => $productVariantImage
        ]);
    }


    public function store(StoreProductVariantImageRequest $request)
    {
        $data = $request->validated();

        // Nếu người dùng không nhập sort_order
        // thì hệ thống tự tính theo từng biến thể sản phẩm
        if (!$request->filled('sort_order')) {
            $maxSortOrder = ProductVariantImage::where('product_variant_id', $data['product_variant_id'])
                ->max('sort_order');

            $data['sort_order'] = $maxSortOrder === null ? 0 : $maxSortOrder + 1;
        }

        $productVariantImage = ProductVariantImage::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Thêm ảnh biến thể thành công',
            'data' => $productVariantImage
        ], 201);
    }

    public function update(UpdateProductVariantImageRequest $request, ProductVariantImage $productVariantImage)
    {
        $data = $request->validated();

        $productVariantImage->update($data);

        $productVariantImage->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật ảnh biến thể thành công',
            'data' => $productVariantImage
        ]);
    }

    public function destroy(ProductVariantImage $productVariantImage)
    {
        $productVariantImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa ảnh biến thể thành công'
        ]);
    }
}
