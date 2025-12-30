<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get a setting by key
     */
    public function show(string $key)
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json([
                'key' => $key,
                'value' => null
            ], 404);
        }

        return response()->json([
            'key' => $setting->key,
            'value' => $setting->value
        ]);
    }

    /**
     * Update or create a setting
     */
    public function upsert(Request $request, string $key)
    {
        $validated = $request->validate([
            'value' => 'required',
            'description' => 'nullable|string'
        ]);

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $validated['value'],
                'description' => $validated['description'] ?? null
            ]
        );

        return response()->json([
            'key' => $setting->key,
            'value' => $setting->value
        ]);
    }

    /**
     * Delete a setting
     */
    public function destroy(string $key)
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        $setting->delete();
        
        return response()->json(['message' => 'Setting deleted successfully']);
    }
}
