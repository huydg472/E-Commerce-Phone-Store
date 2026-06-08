<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockLogRequest;
use App\Http\Requests\UpdateStockLogRequest;
use App\Models\StockLog;

class StockLogController extends Controller
{
    public function index()
    {
        $stockLog = StockLog::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $stockLog
        ]);
    }

    public function store(StoreStockLogRequest $request)
    {
        $stockLog = StockLog::create($request->validated());

        return response()->json([
            'message' => 'thÃªm thÃ nh cÃ´ng',
            'data' => $stockLog
        ], 201);
    }

    public function show(StockLog $stockLog)
    {
        return response()->json([
            'data' => $stockLog
        ]);
    }

    public function update(UpdateStockLogRequest $request, StockLog $stockLog)
    {
        $stockLog->update($request->validated());

        return response()->json([
            'message' => 'update thÃ nh cÃ´ng',
            'data' => $stockLog
        ]);
    }

    public function destroy(StockLog $stockLog)
    {
        $stockLog->delete();

        return response()->json([
            'message' => 'xÃ³a thÃ nh cÃ´ng',
        ]);
    }
}
