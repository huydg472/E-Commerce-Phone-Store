<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $cartItems = CartItem::latest()->get();

        return response()->json([
            'success' => true,
            'message' => "Lấy dữ liệu thành công",
            'data' => $cartItems
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $cartItems = CartItem::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Thêm dữ liệu thành công",
            'data' => $cartItems
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CartItem $cartItem): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết dữ liệu thành công',
            'data' => $cartItem,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $cartItem->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cập nhật dữ liệu thành công",
            'data' => $cartItem
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem): JsonResponse
    {
        try {
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => "Xoá dữ liệu thành công",
                'data' => null
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Xoá dữ liệu thất bại",
            ], 409);
        }
    }
}
