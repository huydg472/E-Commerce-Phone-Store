<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['brand', 'category', 'productVariants.images'])
            ->latest();

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
            'message' => 'Them thanh cong',
            'data' => $product,
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['brand', 'category', 'productVariants.images']);
        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'data' => $product,
        ]);
    }

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
            'message' => 'Cap nhat thanh cong',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return response()->json([
                'status' => true,
                'message' => 'Xoa thanh cong',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete product', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Xoa that bai',
            ], 500);
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
            'message' => 'Cap nhat trang thai san pham thanh cong.',
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
            'message' => 'Lay chi tiet san pham theo slug thanh cong.',
            'data' => $product,
        ]);
    }
}
