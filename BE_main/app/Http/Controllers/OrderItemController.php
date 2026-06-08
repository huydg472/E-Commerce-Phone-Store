<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Http\Requests\StoreOrderItemRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $orderItems = OrderItem::with(['productVariant.product'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data' => $orderItems,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderItemRequest $request): JsonResponse
    {
        $orderItem = OrderItem::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo dữ liệu thành công',
            'data' => $orderItem,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem): JsonResponse
    {
        $orderItem->load(['productVariant.product']);

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết dữ liệu thành công',
            'data' => $orderItem,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderItemRequest $request, OrderItem $orderItem): JsonResponse
    {
        $orderItem->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật dữ liệu thành công',
            'data' => $orderItem,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderItem $orderItem): JsonResponse
    {
        try {
            $orderItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa dữ liệu thành công',
                'data' => null,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa dữ liệu thất bại.',
            ], 409);
        }
    }
}
