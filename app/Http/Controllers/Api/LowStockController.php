<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class LowStockController extends Controller
{
    public function __invoke(Request $request)
    {
        $products = Product::with(['category', 'supplier'])
            ->lowStock()
            ->orderBy('stock_quantity')
            ->limit($request->integer('limit', 10))
            ->get();

        return response()->json($products);
    }
}
