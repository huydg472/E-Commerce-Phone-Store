<?php

namespace App\Http\Controllers;

use App\Models\StockLog;
use App\Http\Requests\StoreStockLogRequest;
use App\Http\Requests\UpdateStockLogRequest;

class StockLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockLog = StockLog::all();
        return response()->json([
            'data' => $stockLog
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockLogRequest $request)
    {
        $stockLog = StockLog::create([
            'product_variant_id' => $request->product_variant_id,
            'user_id' => $request->user_id,
            'order_id' => $request->order_id,
            'type' => $request->type,
            'quantity_before' => $request->quantity_before,
            'quantity_change' => $request->quantity_change,
            'quantity_after' => $request->quantity_after,
            'note' => $request->note
        ]);
        return response()->json([
            'message' => 'thêm thành công',
            'data' => $stockLog
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(StockLog $stockLog)
    {
        return response()->json([
            'data' => $stockLog
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockLogRequest $request, StockLog $stockLog)
    {
        $stockLog->update([
            'product_variant_id' => $request->product_variant_id,
            'user_id' => $request->user_id,
            'order_id' => $request->order_id,
            'type' => $request->type,
            'quantity_before' => $request->quantity_before,
            'quantity_change' => $request->quantity_change,
            'quantity_after' => $request->quantity_after,
            'note' => $request->note
        ]);
        return response()->json([
            'message' => 'update thành công',
            'data' => $stockLog
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockLog $stockLog)
    {
        $stockLog->delete();
        return response()->json([
            'message' => 'xóa thành công',

        ]);
    }
}
