<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShippingAddressRequest;
use App\Http\Requests\UpdateShippingAddressRequest;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ShippingAddress::query()->orderByDesc('id');

        if (!$request->user()->isAdminOrStaff()) {
            $query->where('user_id', $request->user()->id);
        }

        $shippingAddress = $query->get();

        return response()->json([
            'data' => $shippingAddress
        ]);
    }

    public function store(StoreShippingAddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $shippingAddress = DB::transaction(function () use ($data) {
            if (!empty($data['is_default'])) {
                ShippingAddress::where('user_id', $data['user_id'])
                    ->update(['is_default' => false]);
            }

            return ShippingAddress::create($data);
        });

        return response()->json([
            'message' => 'thÃªm thÃ nh cÃ´ng',
            'data' => $shippingAddress
        ]);
    }

    public function show(Request $request, ShippingAddress $shippingAddress): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff() && $shippingAddress->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return response()->json([
            'data' => $shippingAddress
        ]);
    }

    public function update(UpdateShippingAddressRequest $request, ShippingAddress $shippingAddress): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff() && $shippingAddress->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        DB::transaction(function () use ($shippingAddress, $data) {
            if (!empty($data['is_default'])) {
                ShippingAddress::where('user_id', $data['user_id'])
                    ->where('id', '!=', $shippingAddress->id)
                    ->update(['is_default' => false]);
            }

            $shippingAddress->update($data);
        });

        return response()->json([
            'message' => 'update thÃ nh cÃ´ng',
            'data' => $shippingAddress
        ]);
    }

    public function destroy(Request $request, ShippingAddress $shippingAddress): JsonResponse
    {
        if (!$request->user()->isAdminOrStaff() && $shippingAddress->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $shippingAddress->delete();

        return response()->json([
            'message' => 'xÃ³a thÃ nh cÃ´ng'
        ]);
    }
}
