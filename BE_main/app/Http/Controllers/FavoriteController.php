<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    private array $favoriteRelations = [
        'productVariant.images',
        'productVariant.product.brand',
        'productVariant.product.category',
    ];

    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->with($this->favoriteRelations)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $favorites,
        ]);
    }

    public function toggle(Request $request, ProductVariant $productVariant): JsonResponse
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('product_variant_id', $productVariant->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'status' => true,
                'message' => 'Da bo khoi yeu thich.',
                'data' => [
                    'product_id' => $productVariant->product_id,
                    'product_variant_id' => $productVariant->id,
                    'is_favorite' => false,
                ],
            ]);
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'product_variant_id' => $productVariant->id,
        ]);

        $favorite->load($this->favoriteRelations);

        return response()->json([
            'status' => true,
            'message' => 'Da them vao yeu thich.',
            'data' => [
                'product_id' => $productVariant->product_id,
                'product_variant_id' => $productVariant->id,
                'is_favorite' => true,
                'favorite' => $favorite,
            ],
        ]);
    }
}
