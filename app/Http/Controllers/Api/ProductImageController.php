<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
        ]);

        try {
            $image = $request->file('image');
            
            // Generate unique filename
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Store in public disk under products directory
            $path = $image->storeAs('products', $filename, 'public');
            
            // Generate public URL using APP_URL from .env (important for LAN hosting)
            $url = url('/storage/' . $path);
            
            return response()->json([
                'url' => $url,
                'path' => $path,
                'message' => 'Image uploaded successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        try {
            if (Storage::disk('public')->exists($request->path)) {
                Storage::disk('public')->delete($request->path);
            }

            return response()->json([
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
