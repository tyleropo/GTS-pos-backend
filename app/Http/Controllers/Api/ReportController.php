<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate sales report with date range filters.
     */
    public function salesReport(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Transaction::query()
            ->when($validated['date_from'] ?? null, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($validated['date_to'] ?? null, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        // Clone the query for different aggregations
        $totalRevenue = (clone $query)->sum('total');
        $totalTransactions = (clone $query)->count();
        $totalTax = (clone $query)->sum('tax');
        $totalDiscount = (clone $query)->sum('discount');
        $averageTransaction = (clone $query)->avg('total');

        $dailySales = (clone $query)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('sum(total) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $transactionsByStatus = (clone $query)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total) as total'))
            ->groupBy('status')
            ->get();

        $stats = [
            'total_revenue' => $totalRevenue ?? 0,
            'total_transactions' => $totalTransactions,
            'total_tax' => $totalTax ?? 0,
            'total_discount' => $totalDiscount ?? 0,
            'average_transaction' => $averageTransaction ?? 0,
            'daily_sales' => $dailySales,
            'transactions_by_status' => $transactionsByStatus,
        ];

        return response()->json($stats);
    }

    /**
     * Get current inventory status and low stock alerts.
     */
    public function inventoryReport(Request $request)
    {
        $lowStockThreshold = $request->integer('threshold', 10);

        $stats = [
            'total_products' => Product::count(),
            'total_stock_value' => Product::sum(DB::raw('stock_quantity * cost_price')) ?? 0,
            'low_stock_products' => Product::where('stock_quantity', '<=', $lowStockThreshold)
                ->orderBy('stock_quantity')
                ->get(),
            'out_of_stock_products' => Product::where('stock_quantity', 0)->count(),
            'products_by_category' => DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    DB::raw('COALESCE(categories.name, "Uncategorized") as category'),
                    DB::raw('count(products.id) as count')
                )
                ->groupBy('categories.name')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Customer purchase history and analytics.
     */
    public function customerReport(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $stats = [
            'total_customers' => Customer::count(),
            'customers_by_type' => Customer::select('type', DB::raw('count(*) as count'))
                ->whereNotNull('type')
                ->groupBy('type')
                ->get(),
            'top_customers' => Customer::select('customers.*', DB::raw('COALESCE(SUM(transactions.total), 0) as total_spent'))
                ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
                ->when($dateFrom, function ($q, $date) {
                    $q->whereDate('transactions.created_at', '>=', $date);
                })
                ->when($dateTo, function ($q, $date) {
                    $q->whereDate('transactions.created_at', '<=', $date);
                })
                ->groupBy('customers.id')
                ->orderByDesc('total_spent')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Track government customer markups and purchases.
     */
    public function governmentMarkupReport(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $governmentPurchases = PurchaseOrder::select(
            'purchase_orders.*',
            'customers.name as customer_name',
            'customers.type as customer_type'
        )
            ->join('customers', 'purchase_orders.customer_id', '=', 'customers.id')
            ->where('customers.type', 'Government')
            ->when($dateFrom, function ($q, $date) {
                $q->whereDate('purchase_orders.created_at', '>=', $date);
            })
            ->when($dateTo, function ($q, $date) {
                $q->whereDate('purchase_orders.created_at', '<=', $date);
            })
            ->with('products')
            ->get();

        $stats = [
            'total_government_orders' => $governmentPurchases->count(),
            'total_government_revenue' => $governmentPurchases->sum('total'),
            'government_orders' => $governmentPurchases,
        ];

        return response()->json($stats);
    }

    /**
     * Payment status and collection reports.
     */
    public function paymentReport(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $paymentsQuery = Payment::query()
            ->when($dateFrom, function ($q, $date) {
                $q->whereDate('payment_date', '>=', $date);
            })
            ->when($dateTo, function ($q, $date) {
                $q->whereDate('payment_date', '<=', $date);
            });

        $purchaseOrdersQuery = PurchaseOrder::query()
            ->when($dateFrom, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($dateTo, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        $stats = [
            'total_payments_collected' => (clone $paymentsQuery)->sum('amount'),
            'total_payments_count' => (clone $paymentsQuery)->count(),
            'payments_by_method' => (clone $paymentsQuery)->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                ->groupBy('payment_method')
                ->get(),
            'outstanding_balance' => (clone $purchaseOrdersQuery)->where('payment_status', '!=', 'paid')->sum('balance'),
            'orders_by_payment_status' => (clone $purchaseOrdersQuery)->select('payment_status', DB::raw('count(*) as count'), DB::raw('sum(total) as total_amount'))
                ->groupBy('payment_status')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Export report to CSV.
     */
    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', 'in:sales,inventory,customer,government,payment'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        // Call the appropriate report method
        $reportData = match ($validated['report_type']) {
            'sales' => $this->salesReport($request),
            'inventory' => $this->inventoryReport($request),
            'customer' => $this->customerReport($request),
            'government' => $this->governmentMarkupReport($request),
            'payment' => $this->paymentReport($request),
        };

        // Get the JSON data
        $data = $reportData->getData();

        // Convert to CSV format (simple implementation)
        $filename = $validated['report_type'] . '_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write data (simplified - you might want to format this better)
            fputcsv($file, ['Report Data']);
            fputcsv($file, [json_encode($data, JSON_PRETTY_PRINT)]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
