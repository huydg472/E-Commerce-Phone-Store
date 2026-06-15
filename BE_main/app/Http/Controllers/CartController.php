<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Cart;
use App\Services\CartPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartPricingService $pricingService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Cart::with(['items.productVariant.product'])->latest();

        if (!$request->user()->isAdminOrStaff()) {
            $query->where('user_id', $request->user()->id);
        }

        $carts = $query->get();

        $carts->each(fn (Cart $cart) => $this->pricingService->applyPricing($cart));

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data' => $carts,
        ], 200);
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            [
                'status' => $data['status'] ?? 'active',
            ]
        );

        if (isset($data['status'])) {
            $cart->update(['status' => $data['status']]);
        }

        $cart->load(['items.productVariant.product']);
        $cart->setAttribute('subtotal', 0);
        $cart->setAttribute('total_amount', 0);

        return response()->json([
            'success' => true,
            'message' => 'Tạo dữ liệu thành công',
            'data' => $cart,
        ], 201);
    }

    public function show(Request $request): JsonResponse
    {
        $cart = Cart::with(['items.productVariant.product'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'message' => 'Giỏ hàng trống.',
                'data' => [
                    'items' => [],
                    'subtotal' => 0,
                    'total_amount' => 0,
                ],
            ], 200);
        }

        $this->pricingService->applyPricing($cart);

        return response()->json([
            'success' => true,
            'message' => 'Lấy giỏ hàng thành công.',
            'data' => [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
                'items' => $cart->items,
                'subtotal' => $cart->subtotal,
                'total_amount' => $cart->total_amount,
            ],
        ], 200);
    }

    public function update(UpdateCartRequest $request, Cart $cart): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff() && $cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $cart->update($request->validated());
        $cart->load(['items.productVariant.product']);

        $this->pricingService->applyPricing($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật dữ liệu thành công',
            'data' => $cart,
        ], 200);
    }

    public function destroy(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'message' => 'Giỏ hàng trống.',
                'data' => null,
            ], 200);
        }

        $cart->cartItems()->delete();
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xoá dữ liệu thành công',
            'data' => null,
        ], 200);
    }
}
