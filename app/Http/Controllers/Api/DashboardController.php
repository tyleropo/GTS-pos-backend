<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Repair;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    /**
     * Get dashboard metrics
     */
    public function metrics(Request $request)
    {
        // Calculate key metrics
        $totalRevenue = Transaction::where('status', 'completed')
            ->sum('total_amount');
        
        $totalProducts = Product::count();
        
        $totalTransactions = Transaction::count();
        
        $todayTransactions = Transaction::whereDate('created_at', today())
            ->count();
        
        $yesterdayTransactions = Transaction::whereDate('created_at', today()->subDay())
            ->count();
        
        $todayRevenue = Transaction::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $yesterdayRevenue = Transaction::where('status', 'completed')
            ->whereDate('created_at', today()->subDay())
            ->sum('total_amount');
        
        // Calculate percentage changes
        $revenueChange = $yesterdayRevenue > 0 
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 
            : 0;
        
        $transactionsChange = $yesterdayTransactions > 0 
            ? (($todayTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100 
            : 0;
        
        return response()->json([
            'cards' => [
                [
                    'title' => 'Total Revenue',
                    'value' => '₱' . number_format($totalRevenue, 2),
                    'percentage' => round($revenueChange, 1),
                    'trend' => $revenueChange >= 0 ? 'up' : 'down',
                    'hint' => 'vs yesterday',
                ],
                [
                    'title' => 'Total Products',
                    'value' => $totalProducts,
                    'trend' => 'neutral',
                ],
                [
                    'title' => 'Transactions Today',
                    'value' => $todayTransactions,
                    'percentage' => round($transactionsChange, 1),
                    'trend' => $transactionsChange >= 0 ? 'up' : 'down',
                    'hint' => 'vs yesterday',
                ],
                [
                    'title' => 'Total Transactions',
                    'value' => $totalTransactions,
                    'trend' => 'neutral',
                ],
            ],
        ]);
    }
    
    /**
     * Get recent activity
     */
    public function recentActivity(Request $request)
    {
        $activities = Transaction::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'title' => 'Transaction #' . $transaction->transaction_number,
                    'description' => 'Customer: ' . ($transaction->customer->name ?? 'Walk-in'),
                    'time' => $transaction->created_at->diffForHumans(),
                    'created_at' => $transaction->created_at->toISOString(),
                    'meta' => [
                        'amount' => $transaction->total_amount,
                        'status' => $transaction->status,
                    ],
                ];
            });
        
        return response()->json($activities);
    }
    
    /**
     * Get low stock products
     */
    public function lowStock(Request $request)
    {
        $products = Product::with(['category', 'supplier'])
            ->whereRaw('stock_quantity <= reorder_level')
            ->orWhere('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'supplier_id' => $product->supplier_id,
                    'supplier' => $product->supplier ? [
                        'id' => $product->supplier->id,
                        'name' => $product->supplier->business_name ?? $product->supplier->contact_person,
                    ] : null,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'stock_quantity' => $product->stock_quantity,
                    'reorder_level' => $product->reorder_level,
                    'unit' => $product->unit,
                    'barcode' => $product->barcode,
                    'created_at' => $product->created_at?->toISOString(),
                    'updated_at' => $product->updated_at?->toISOString(),
                ];
            });
        
        return response()->json($products);
    }
    
    /**
     * Get top selling products
     */
    public function topSelling(Request $request)
    {
        $products = Product::with(['category', 'supplier'])
            ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->select('products.*', DB::raw('SUM(transaction_items.quantity) as total_sold'))
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'supplier_id' => $product->supplier_id,
                    'supplier' => $product->supplier ? [
                        'id' => $product->supplier->id,
                        'name' => $product->supplier->business_name ?? $product->supplier->contact_person,
                    ] : null,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'stock_quantity' => $product->stock_quantity,
                    'reorder_level' => $product->reorder_level,
                    'unit' => $product->unit,
                    'barcode' => $product->barcode,
                    'total_sold' => $product->total_sold ?? 0,
                    'created_at' => $product->created_at?->toISOString(),
                    'updated_at' => $product->updated_at?->toISOString(),
                ];
            });
        
        return response()->json($products);
    }
    
    /**
     * Get pending repairs
     */
    public function pendingRepairs(Request $request)
    {
        $repairs = Repair::with('customer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($repair) {
                return [
                    'id' => $repair->id,
                    'ticket_number' => $repair->ticket_number,
                    'customer_name' => $repair->customer->name ?? 'Unknown',
                    'status' => $repair->status,
                ];
            });
        
        return response()->json($repairs);
    }
}
