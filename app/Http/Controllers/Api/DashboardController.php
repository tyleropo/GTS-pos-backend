<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Repair;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics()
    {
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
        $yesterdayRevenue = Transaction::whereDate('created_at', today()->subDay())->sum('total');
        $revenueTrend = $yesterdayRevenue > 0 
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 
            : 0;

        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $pendingOrders = \App\Models\CustomerOrder::where('status', 'submitted')->count(); // Pending/Submitted orders
        $inProgressOrders = \App\Models\CustomerOrder::where('status', 'fulfilled')->count(); // Just for hint? Or maybe partial?
        // Actually, let's just show Pending Orders
        
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();
        $pendingRepairs = Repair::pending()->count();
        $inProgressRepairs = Repair::where('status', 'in_progress')->count();

        return response()->json([
            [
                'title' => 'Today\'s Revenue',
                'value' => '₱' . number_format($todayRevenue, 2),
                'trend' => $revenueTrend >= 0 ? 'up' : 'down',
                'percentage' => abs(round($revenueTrend, 1)),
                'hint' => $todayTransactions . ' transactions today',
                'icon' => 'revenue',
                'href' => '/transactions',
            ],
            [
                'title' => 'Pending Orders',
                'value' => $pendingOrders,
                'trend' => 'neutral',
                'hint' => 'Customer orders to fulfill',
                'icon' => 'orders',
                'href' => '/customer-orders',
            ],
            [
                'title' => 'Low Stock Items',
                'value' => $lowStockCount,
                'trend' => $lowStockCount > 0 ? 'down' : 'up',
                'hint' => $lowStockCount > 0 ? 'Needs restocking' : 'Stock levels healthy',
                'icon' => 'low-stock',
                'href' => '/inventory?filter=low-stock',
            ],
            [
                'title' => 'Pending Repairs',
                'value' => $pendingRepairs,
                'trend' => 'neutral',
                'hint' => $inProgressRepairs . ' in progress',
                'icon' => 'repairs',
                'href' => '/repairs',
            ],
        ]);
    }

    public function recentActivity()
    {
        $transactions = Transaction::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => 'Sale: ' . $t->invoice_number,
                'description' => ($t->customer ? $t->customer->name : 'Walk-in') . ' - ₱' . number_format($t->total, 2),
                'time' => $t->created_at->toIso8601String(),
                'type' => 'transaction',
            ]);

        $repairs = Repair::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => 'Repair: ' . $r->ticket_number,
                'description' => $r->customer?->name . ' - ' . $r->device,
                'time' => $r->created_at->toIso8601String(),
                'type' => 'repair',
            ]);

        return response()->json(
            $transactions->merge($repairs)
                ->sortByDesc('time')
                ->take(10)
                ->values()
        );
    }

    public function lowStock()
    {
        $products = Product::with(['category', 'supplier'])
            ->lowStock()
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function topSelling()
    {
        $products = Product::with(['category', 'supplier'])
            ->select('products.*')
            ->leftJoin('product_transaction', 'products.id', '=', 'product_transaction.product_id')
            ->selectRaw('COALESCE(SUM(product_transaction.quantity), 0) as total_sold')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function pendingRepairs()
    {
        $repairs = Repair::with('customer')
            ->pending()
            ->orderBy('promised_at')
            ->limit(10)
            ->get();

        return response()->json($repairs);
    }

    public function calendarEvents()
    {
        // Get PO delivery dates (non-cancelled, with expected_at)
        $poEvents = \App\Models\PurchaseOrder::with('supplier')
            ->whereNotNull('expected_at')
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->map(fn ($po) => [
                'id' => 'po-' . $po->id,
                'title' => $po->po_number . ' - ' . ($po->supplier?->company_name ?? 'Unknown'),
                'start' => $po->expected_at->format('Y-m-d'),
                'color' => $po->status === 'received' ? '#22c55e' : '#3b82f6', // green if received, blue otherwise
                'extendedProps' => [
                    'type' => 'po',
                    'id' => $po->id,
                    'status' => $po->status,
                ],
            ]);

        // Get PO payment due dates (non-cancelled, non-paid, with payment_due_date)
        $poPaymentEvents = \App\Models\PurchaseOrder::with('supplier')
            ->whereNotNull('payment_due_date')
            ->whereNotIn('status', ['cancelled'])
            ->where('payment_status', '!=', 'paid')
            ->get()
            ->map(fn ($po) => [
                'id' => 'po-payment-' . $po->id,
                'title' => $po->po_number . ' - ' . ($po->supplier?->company_name ?? 'Unknown'),
                'start' => $po->payment_due_date->format('Y-m-d'),
                'color' => '#f59e0b', // amber for payment due
                'extendedProps' => [
                    'type' => 'po-payment',
                    'id' => $po->id,
                    'status' => $po->payment_status,
                ],
            ]);

        // Get Customer Order dates (non-cancelled, with expected_at)
        $coEvents = \App\Models\CustomerOrder::with('customer')
            ->whereNotNull('expected_at')
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->map(fn ($co) => [
                'id' => 'co-' . $co->id,
                'title' => $co->co_number . ' - ' . ($co->customer?->name ?? 'Unknown'),
                'start' => $co->expected_at->format('Y-m-d'),
                'color' => $co->status === 'fulfilled' ? '#22c55e' : '#8b5cf6', // green if fulfilled, purple otherwise
                'extendedProps' => [
                    'type' => 'co',
                    'id' => $co->id,
                    'status' => $co->status,
                ],
            ]);

        // Get Repair completion dates (non-completed, non-cancelled, with promised_at)
        $repairEvents = Repair::with('customer')
            ->whereNotNull('promised_at')
            ->get()
            ->map(fn ($r) => [
                'id' => 'repair-' . $r->id,
                'title' => $r->ticket_number . ' - ' . ($r->customer?->name ?? 'Walk-in'),
                'start' => $r->promised_at->format('Y-m-d'),
                'color' => $r->status === 'completed' ? '#22c55e' : '#f97316', // green if completed, orange otherwise
                'extendedProps' => [
                    'type' => 'repair',
                    'id' => $r->id,
                    'status' => $r->status,
                ],
            ]);

        return response()->json($poEvents->merge($poPaymentEvents)->merge($coEvents)->merge($repairEvents)->values());
    }
    public function dailyTransactions()
    {
        $transactions = Transaction::with('customer')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'invoice_number' => $t->invoice_number,
                'customer' => $t->customer ? $t->customer->name : 'Walk-in',
                'total' => $t->total,
                'status' => $t->payment_status ?? 'paid', // Assuming payment_status or fallback
                'time' => $t->created_at->format('h:i A'),
            ]);

        return response()->json($transactions);
    }
}
