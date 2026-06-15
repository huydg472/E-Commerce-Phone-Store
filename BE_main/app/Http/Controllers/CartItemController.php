<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartPricingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function __construct(
        private readonly CartPricingService $pricingService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $cart = Cart::with(['items.productVariant.product'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json([
                'status' => true,
                'message' => 'Giỏ hàng trống.',
                'data' => [
                    'items' => [],
                    'subtotal' => 0,
                    'total_amount' => 0,
                ]
            ], 200);
        }

        $this->pricingService->applyPricing($cart);

        return response()->json([
            'status' => true,
            'message' => 'Lấy giỏ hàng thành công.',
            'data' => [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
                'items' => $cart->items,
                'subtotal' => $cart->subtotal,
                'total_amount' => $cart->total_amount,
            ]
        ], 200);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $user = $request->user();
        $quantity = max((int) ($request->quantity ?? 1), 1);

        $variant = ProductVariant::with(['product.brand', 'product.category'])
            ->findOrFail($request->product_variant_id);

        if (
            $variant->status !== 'active' ||
            $variant->product?->status !== 'active' ||
            $variant->product?->brand?->status !== 'active' ||
            $variant->product?->category?->status !== 'active'
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm này hiện không còn bán.'
            ], 400);
        }

        if ((int) $variant->available_quantity < $quantity) {
            return response()->json([
                'status' => false,
                'message' => 'Số lượng tồn kho không đủ.'
            ], 400);
        }

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'active',
            ],
            [
                'user_id' => $user->id,
                'status' => 'active',
            ]
        );

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;

            if ((int) $variant->available_quantity < $newQuantity) {
                return response()->json([
                    'status' => false,
                    'message' => 'Số lượng trong giỏ vượt quá tồn kho.'
                ], 400);
            }

            $cartItem->update([
                'quantity' => $newQuantity
            ]);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
        }

        $cartItem->load(['productVariant.product']);

        return response()->json([
            'status' => true,
            'message' => 'Thêm vào giỏ hàng thành công.',
            'data' => $cartItem
        ], 201);
    }

    public function show(Request $request, CartItem $cartItem): JsonResponse
    {
        $cartItem->load(['cart', 'productVariant.product.brand', 'productVariant.product.category']);

        if (!$request->user()->isAdminOrStaff() && $cartItem->cart?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết dữ liệu thành công',
            'data' => $cartItem,
        ], 200);
    }

    public function update(Request $request, UpdateCartItemRequest $updateRequest, CartItem $cartItem): JsonResponse
    {
        $cartItem->load(['cart', 'productVariant.product']);

        if (!$request->user()->isAdminOrStaff() && $cartItem->cart?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $updateRequest->validated();
        $variant = $cartItem->productVariant;

        if (
            !$variant ||
            $variant->status !== 'active' ||
            $variant->product?->status !== 'active' ||
            $variant->product?->brand?->status !== 'active' ||
            $variant->product?->category?->status !== 'active'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm này hiện không còn bán.',
            ], 400);
        }

        if ((int) $variant->available_quantity < (int) $data['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng tồn kho không đủ.',
            ], 400);
        }

        $cartItem->update($data);
        $cartItem->load(['cart', 'productVariant.product']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật dữ liệu thành công',
            'data' => $cartItem
        ], 200);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $cartItem->load('cart');

        if (!$request->user()->isAdminOrStaff() && $cartItem->cart?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        try {
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xoá dữ liệu thành công',
                'data' => null
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xoá dữ liệu thất bại',
            ], 409);
        }
    }
}
