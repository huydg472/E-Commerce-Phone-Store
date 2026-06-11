<?php

namespace App\Http\Controllers;

use App\Models\ProductVariantImage;
use App\Http\Requests\StoreProductVariantImageRequest;
use App\Http\Requests\UpdateProductVariantImageRequest;
use Illuminate\Support\Facades\Storage;

class ProductVariantImageController extends Controller
{
    private function storeImageFile(StoreProductVariantImageRequest|UpdateProductVariantImageRequest $request): ?string
    {
        if (! $request->hasFile('image_file')) {
            return null;
        }

        $path = $request->file('image_file')->store('product-variants', 'public');

        return $request->getSchemeAndHttpHost() . '/storage/' . $path;
    }

    private function deleteStoredImage(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $storagePrefix = '/storage/';

        if (! str_starts_with($path, $storagePrefix)) {
            return;
        }

        Storage::disk('public')->delete(substr($path, strlen($storagePrefix)));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productVariantImage = ProductVariantImage::query()
            ->with(['productVariant.product'])
            ->orderByDesc('id')
            ->get();
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
        $productVariantImage->load(['productVariant.product']);

        return response()->json([
            'data' => $productVariantImage
        ]);
    }


    public function store(StoreProductVariantImageRequest $request)
    {
        $data = $request->validated();
        unset($data['image_file']);

        if ($imageUrl = $this->storeImageFile($request)) {
            $data['image_url'] = $imageUrl;
        }

        // Nếu người dùng không nhập sort_order
        // thì hệ thống tự tính theo từng biến thể sản phẩm
        if (!$request->filled('sort_order')) {
            $maxSortOrder = ProductVariantImage::where('product_variant_id', $data['product_variant_id'])
                ->max('sort_order');

            $data['sort_order'] = $maxSortOrder === null ? 0 : $maxSortOrder + 1;
        }

        $productVariantImage = ProductVariantImage::create($data);
        $productVariantImage->load(['productVariant.product']);

        return response()->json([
            'status' => true,
            'message' => 'Thêm ảnh biến thể thành công',
            'data' => $productVariantImage
        ], 201);
    }

    public function update(UpdateProductVariantImageRequest $request, ProductVariantImage $productVariantImage)
    {
        $data = $request->validated();
        unset($data['image_file']);

        if ($imageUrl = $this->storeImageFile($request)) {
            $this->deleteStoredImage($productVariantImage->image_url);
            $data['image_url'] = $imageUrl;
        }

        $productVariantImage->update($data);

        $productVariantImage->refresh();
        $productVariantImage->load(['productVariant.product']);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật ảnh biến thể thành công',
            'data' => $productVariantImage
        ]);
    }

    public function destroy(ProductVariantImage $productVariantImage)
    {
        $this->deleteStoredImage($productVariantImage->image_url);
        $productVariantImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa ảnh biến thể thành công'
        ]);
    }
}
