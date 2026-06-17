<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $payment = Payment::with([
            'order.user',
            'order.orderItems.productVariant.product',
        ])
            ->latest()
            ->get();

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
        $payment->load([
            'order.user',
            'order.orderItems.productVariant.product',
        ]);

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
        $payment->load([
            'order.user',
            'order.orderItems.productVariant.product',
        ]);

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
        $payment->load([
            'order.user',
            'order.orderItems.productVariant.product',
        ]);

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
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xoá dữ liệu thành công',
            'data' => null,
        ], 200);
    }

    public function createVnpayUrl(Request $request, Payment $payment): JsonResponse
    {
        $payment->loadMissing('order');
        $user = $request->user();

        if (!$payment->order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        if (!$user->isAdminOrStaff() && $payment->order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        if ($payment->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã được thanh toán.',
            ], 409);
        }

        if ($payment->order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã bị huỷ, không thể tạo link thanh toán.',
            ], 409);
        }

        $config = config('services.vnpay');
        $vnpUrl = $config['url'] ?? null;
        $tmnCode = $config['tmn_code'] ?? null;
        $hashSecret = $config['hash_secret'] ?? null;
        $returnUrl = $config['return_url'] ?? null;

        if (!$vnpUrl || !$tmnCode || !$hashSecret || !$returnUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu cấu hình VNPay.',
            ], 500);
        }

        $inputData = [
            'vnp_Version' => $config['version'] ?? '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (int)round(((float)$payment->amount) * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => (string)$payment->id,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $payment->order->order_code,
            'vnp_OrderType' => 'billpayment',
            'vnp_Locale' => $config['locale'] ?? 'vn',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr' => $request->ip(),
            'vnp_CreateDate' => now()->format('YmdHis'),
        ];

        [$query, $hashData] = $this->buildVnpayQuery($inputData);
        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);
        $paymentUrl = $vnpUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;

        return response()->json([
            'success' => true,
            'message' => 'Tạo URL VNPay thành công.',
            'data' => [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_url' => $paymentUrl,
            ],
        ], 200);
    }

    public function vnpayReturn(Request $request): RedirectResponse
    {
        $config = config('services.vnpay');
        $frontendUrl = rtrim($config['frontend_url'] ?? config('app.url'), '/');
        $hashSecret = $config['hash_secret'] ?? null;

        if (!$hashSecret) {
            return redirect()->away($frontendUrl . '/order-success?payment=failed&reason=missing_config');
        }

        $inputData = $request->all();
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? '';

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        [$query, $hashData] = $this->buildVnpayQuery($inputData);
        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        if (!hash_equals($secureHash, $vnpSecureHash)) {
            return redirect()->away($frontendUrl . '/order-success?payment=failed&reason=invalid_signature');
        }

        $payment = Payment::with('order')->find((int)$request->input('vnp_TxnRef'));

        if (!$payment || !$payment->order) {
            return redirect()->away($frontendUrl . '/order-success?payment=failed&reason=payment_not_found');
        }

        if ($payment->order->order_status === 'cancelled') {
            return redirect()->away($frontendUrl . '/order-success?order_id=' . $payment->order_id . '&payment=failed&reason=order_cancelled');
        }

        $transactionStatus = $request->input('vnp_TransactionStatus');
        $isSuccess = $request->input('vnp_ResponseCode') === '00'
            && ($transactionStatus === null || $transactionStatus === '00');

        $currentPaymentStatus = strtolower((string)$payment->payment_status);
        $currentOrderPaymentStatus = strtolower((string)$payment->order->payment_status);

        if ($isSuccess && ($currentPaymentStatus === 'paid' || $currentOrderPaymentStatus === 'paid')) {
            return redirect()->away($frontendUrl . '/order-success?order_id=' . $payment->order_id . '&payment=success');
        }

        if (!$isSuccess && ($currentPaymentStatus === 'failed' || $currentOrderPaymentStatus === 'failed')) {
            return redirect()->away($frontendUrl . '/order-success?order_id=' . $payment->order_id . '&payment=failed');
        }

        DB::transaction(function () use ($payment, $request, $isSuccess) {
            $payment->update([
                'payment_method' => 'vnpay',
                'payment_status' => $isSuccess ? 'paid' : 'failed',
                'transaction_code' => $request->input('vnp_TransactionNo') ?? $payment->transaction_code,
                'paid_at' => $isSuccess ? ($payment->paid_at ?? now()) : $payment->paid_at,
            ]);

            $payment->order->update([
                'payment_status' => $isSuccess ? 'paid' : 'failed',
            ]);
        });

        $status = $isSuccess ? 'success' : 'failed';

        return redirect()->away($frontendUrl . '/order-success?order_id=' . $payment->order_id . '&payment=' . $status);
    }

    private function buildVnpayQuery(array $params): array
    {
        ksort($params);

        $query = '';
        $hashData = '';

        foreach ($params as $key => $value) {
            $encodedKey = urlencode((string)$key);
            $encodedValue = urlencode((string)$value);

            $hashData .= ($hashData === '' ? '' : '&') . $encodedKey . '=' . $encodedValue;
            $query .= $encodedKey . '=' . $encodedValue . '&';
        }

        return [rtrim($query, '&'), $hashData];
    }
}
