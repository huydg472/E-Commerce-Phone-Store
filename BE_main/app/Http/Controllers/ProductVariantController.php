<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    private function codePart(?string $value): string
    {
        $code = Str::upper(Str::ascii((string) $value));
        $code = preg_replace('/[^A-Z0-9]+/', '', $code) ?: '';

        return $code ?: 'NA';
    }

    private function productCode(Product $product): string
    {
        $source = Str::upper(Str::ascii($product->slug ?: $product->name));
        $tokens = preg_split('/[^A-Z0-9]+/', $source, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $code = collect($tokens)
            ->map(fn (string $token) => preg_match('/\d/', $token) ? $token : Str::substr($token, 0, 1))
            ->join('');

        return Str::limit($code ?: 'SP' . $product->id, 30, '');
    }

    private function generateSku(array $data, ?int $ignoreId = null): string
    {
        $product = Product::findOrFail($data['product_id']);
        $baseSku = implode('-', [
            $this->productCode($product),
            $this->codePart($data['color'] ?? ''),
            $this->codePart($data['storage'] ?? ''),
            $this->codePart($data['ram'] ?? ''),
        ]);

        $sku = $baseSku;
        $suffix = 2;

        while (ProductVariant::where('sku', $sku)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $sku = Str::limit($baseSku, 95, '') . '-' . $suffix;
            $suffix++;
        }

        return $sku;
    }

    public function index()
    {
        $productVariant = ProductVariant::query()
            ->with(['product', 'images'])
            ->orderByDesc('id')
            ->get();
        return response()->json(['status' => true, 'message' => 'Lay du lieu thanh cong', 'data' => $productVariant]);
    }

    public function store(StoreProductVariantRequest $request)
    {
        $data = $request->validated();
        $data['sku'] = blank($data['sku'] ?? null) ? $this->generateSku($data) : $data['sku'];

        $productVariant = ProductVariant::create($data);

        $productVariant->load(['product', 'images']);

        return response()->json(['status' => true, 'message' => 'Them thanh cong', 'data' => $productVariant]);
    }

    public function show(ProductVariant $productVariant)
    {
        $productVariant->load(['product', 'images']);

        return response()->json(['status' => true, 'message' => 'Lay chi tiet du lieu thanh cong', 'data' => $productVariant]);
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant)
    {
        $data = $request->validated();

        if (array_key_exists('sku', $data) && blank($data['sku'])) {
            $dataForSku = array_merge($productVariant->only(['product_id', 'color', 'storage', 'ram']), $data);
            $data['sku'] = $this->generateSku($dataForSku, $productVariant->id);
        }

        $productVariant->update($data);

        $productVariant->load(['product', 'images']);

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

        $productVariant->load(['product', 'images']);

        return response()->json([
            'status' => true,
            'message' => 'Cap nhat trang thai bien the thanh cong.',
            'data' => $productVariant,
        ]);
    }
}
