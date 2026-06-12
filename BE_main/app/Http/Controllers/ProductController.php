<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function storeThumbnailFile(StoreProductRequest|UpdateProductRequest $request): ?string
    {
        if (! $request->hasFile('thumbnail_file')) {
            return null;
        }

        $path = $request->file('thumbnail_file')->store('products', 'public');

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
        $activeOnly = $request->filled('status') && $request->status === 'active';

        $query = Product::query()
            ->with([
                'brand',
                'category',
                'productVariants' => fn ($query) => $query
                    ->when($activeOnly, fn ($variantQuery) => $variantQuery->where('status', 'active'))
                    ->orderBy('id')
                    ->with('images'),
            ])
            ->orderBy('id');

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($activeOnly) {
            $query
                ->whereHas('brand', fn ($q) => $q->where('status', 'active'))
                ->whereHas('category', fn ($q) => $q->where('status', 'active'))
                ->whereHas('productVariants', fn ($q) => $q->where('status', 'active')->where('quantity', '>', 0));
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        if ($request->get('sort') === 'id_asc') {
            $query->orderBy('id');
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
        $data = $request->validated();

        if ($thumbnailUrl = $this->storeThumbnailFile($request)) {
            $data['thumbnail_url'] = $thumbnailUrl;
        }

        unset($data['thumbnail_file']);

        $product = Product::create([
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
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
        $data = $request->validated();

        if ($thumbnailUrl = $this->storeThumbnailFile($request)) {
            $this->deleteStoredImage($product->thumbnail_url);
            $data['thumbnail_url'] = $thumbnailUrl;
        }

        unset($data['thumbnail_file']);

        $product->update($data);

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
            $this->deleteStoredImage($product->thumbnail_url);
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
            ->where('status', 'active')
            ->whereHas('brand', fn ($q) => $q->where('status', 'active'))
            ->whereHas('category', fn ($q) => $q->where('status', 'active'))
            ->whereHas('productVariants', fn ($q) => $q->where('status', 'active')->where('quantity', '>', 0))
            ->with([
                'brand',
                'category',
                'productVariants' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->with('images'),
            ])
            ->firstOrFail();

        $this->attachDisplayPrice($product);

        return response()->json([
            'status' => true,
            'message' => 'Lay chi tiet san pham theo slug thanh cong.',
            'data' => $product,
        ]);
    }
}
