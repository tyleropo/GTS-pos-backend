<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class BarcodeLookupController extends Controller
{
    public function __invoke(string $code)
    {
        $product = Product::with(['category', 'supplier'])
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->firstOrFail();

        return response()->json($product);
    }
}
