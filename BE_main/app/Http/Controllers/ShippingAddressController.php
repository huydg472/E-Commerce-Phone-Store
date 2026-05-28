<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use App\Http\Requests\StoreShippingAddressRequest;
use App\Http\Requests\UpdateShippingAddressRequest;

class ShippingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shippingAddress = ShippingAddress::query()
            ->orderByDesc('id')
            ->get();
        return response()->json([
            'data' => $shippingAddress
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShippingAddressRequest $request)
    {
        $shippingAddress = ShippingAddress::create([
            'user_id' => $request->user_id,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'province' => $request->province,
            'district' => $request->district,
            'ward' => $request->ward,
            'address_detail' => $request->address_detail,
            'note' => $request->note,
            'is_default' => $request->is_default
        ]);
        return response()->json([
            'message' => 'thêm thành công',
            'data' => $shippingAddress
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingAddress $shippingAddress)
    {
        return response()->json([
            'data' => $shippingAddress
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShippingAddressRequest $request, ShippingAddress $shippingAddress)
    {
        $shippingAddress->update([
            'user_id' => $request->user_id,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'province' => $request->province,
            'district' => $request->district,
            'ward' => $request->ward,
            'address_detail' => $request->address_detail,
            'note' => $request->note,
            'is_default' => $request->is_default
        ]);
        return response()->json([
            'message' => 'update thành công',
            'data' => $shippingAddress
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingAddress $shippingAddress)
    {
        $shippingAddress->delete();
        return response()->json([
            'message' => 'xóa thành công'
        ]);
    }
}
