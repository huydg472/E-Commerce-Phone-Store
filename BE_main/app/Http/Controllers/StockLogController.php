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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(StockLog $stockLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockLogRequest $request, StockLog $stockLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockLog $stockLog)
    {
        //
    }
}
