<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    private function getSettings(): SiteSetting
    {
        return SiteSetting::current();
    }

    public function show(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lay du lieu thanh cong',
            'data' => $this->getSettings(),
        ], 200);
    }

    public function update(UpdateSiteSettingRequest $request): JsonResponse
    {
        $settings = $this->getSettings();
        $settings->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat du lieu thanh cong',
            'data' => $settings->fresh(),
        ], 200);
    }
}
