<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $payment = Payment::latest()->get();

        return response()->json([
            'success' => true,
            'message' => "Lấy dữ liệu thành công",
            'data' => $payment
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = Payment::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Tạo dữ liệu thành công",
            'data' => $payment
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Lấy chi tiết dữ liệu thành công",
            'data' => $payment
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cập nhật dữ liệu thành công",
            'data' => $payment
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
