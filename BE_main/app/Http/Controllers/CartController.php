<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $carts = Cart::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data' => $carts,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCartRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết dữ liệu thành công',
            'data' => $cart,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartRequest $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        //
    }
}
