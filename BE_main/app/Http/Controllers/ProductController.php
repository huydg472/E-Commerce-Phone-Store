<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function attachDisplayPrice(Product $product): Product
    {
        $variant = $product->productVariants->first(function ($variant) {
            return $variant->sale_price !== null && $variant->sale_price !== '';
        }) ?? $product->productVariants->first();

        $product->setAttribute('display_price', $variant?->sale_price ?? $variant?->price ?? null);
        $product->setAttribute('display_old_price', $variant?->price ?? null);

        return $product;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['brand', 'category', 'productVariants.images']);

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        if ($request->get('sort') === 'latest') {
            $query->latest();
        }

        $perPage = $request->integer('per_page', 12);
        $products = $query->paginate($perPage);

        $products->getCollection()->transform(function (Product $product) {
            return $this->attachDisplayPrice($product);
        });

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'thumbnail_url' => $request->thumbnail_url,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->is_featured,
            'status' => $request->status,
        ]);

        $product->load(['brand', 'category', 'productVariants.images']);
        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'message' => 'Thêm thành công',
            'data' => $product,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['brand', 'category', 'productVariants.images']);
        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update([
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'thumbnail_url' => $request->thumbnail_url,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->is_featured,
            'status' => $request->status,
        ]);

        $product->load(['brand', 'category', 'productVariants.images']);
        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
            'data' => $product,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus(Product $product): JsonResponse
    {
        $product->update([
            'status' => $product->status === 'active' ? 'inactive' : 'active',
        ]);

        $product->load(['brand', 'category', 'productVariants.images']);
        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái sản phẩm thành công.',
            'data' => $product,
        ]);
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->with(['brand', 'category', 'productVariants.images'])
            ->firstOrFail();

        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'message' => 'Lấy chi tiết sản phẩm theo slug thành công.',
            'data' => $product,
        ]);
    }
}
