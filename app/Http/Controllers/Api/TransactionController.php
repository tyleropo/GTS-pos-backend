<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with(['customer', 'products'])
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($request->payment_method, fn ($q, $method) => $q->paymentMethod($method))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->customer_ids, function ($q, $ids) {
                // Determine if ids is an array or string
                $idList = is_array($ids) ? $ids : explode(',', $ids);
                $q->whereIn('customer_id', $idList);
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 50));

        // Transform each transaction to include items from products relationship
        $transactions->getCollection()->transform(function ($tx) {
            $txArray = $tx->toArray();
            
            // Replace items array with transformed products
            $txArray['items'] = $tx->products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'unit_price' => $product->pivot->unit_price,
                    'discount' => $product->pivot->discount,
                    'tax' => $product->pivot->tax,
                    'line_total' => $product->pivot->line_total,
                ];
            })->toArray();
            
            // Ensure meta is an object, not null
            if ($txArray['meta'] === null) {
                $txArray['meta'] = new \stdClass();
            }
            
            return $txArray;
        });

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'payment_method' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.product_name' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'meta' => ['nullable', 'array'],
        ]);

        $transaction = DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);

            // Create transaction
            $transaction = Transaction::create([
                'invoice_number' => 'TXN-' . now()->format('Ymd-Hi') . '-' . strtoupper(Str::random(4)),
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'payment_method' => $validated['payment_method'],
                'items' => $items->toArray(),
                'meta' => $validated['meta'] ?? [],
            ]);

            // Attach products and update stock
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Attach to pivot table
                $transaction->products()->attach($item['product_id'], [
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);

                // Update stock
                $oldStock = $product->stock_quantity;
                $product->decrement('stock_quantity', $item['quantity']);
                $newStock = $product->fresh()->stock_quantity;

                // Log stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => -$item['quantity'],
                    'previous_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'reference_type' => Transaction::class,
                    'reference_id' => $transaction->id,
                    'notes' => 'Sale transaction ' . $transaction->invoice_number,
                    'user_id' => auth()->id(),
                ]);
            }

            return $transaction;
        });

        return response()->json($transaction->load(['products', 'customer']));
    }

    public function refund(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'items' => 'nullable|array', // Optional: for partial refunds
            'items.*' => 'uuid|exists:products,id',
        ]);

        $transaction = Transaction::with('products')->findOrFail($id);

        // Check if already refunded
        if ($transaction->status === 'refunded') {
            return response()->json([
                'message' => 'This transaction has already been fully refunded'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $refundAmount = 0;
            $refundedItems = [];

            // Determine what to refund
            $itemsToRefund = $request->items 
                ? $transaction->products->whereIn('id', $request->items)
                : $transaction->products;

            // Calculate refund amount and restore stock
            foreach ($itemsToRefund as $product) {
                $quantity = $product->pivot->quantity;
                $lineTotal = $product->pivot->line_total;
                
                $refundAmount += $lineTotal;
                $refundedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'amount' => $lineTotal,
                ];

                // Restore stock
                $product->increment('stock_quantity', $quantity);

                // Log stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'type' => 'refund',
                    'reference_type' => 'transaction',
                    'reference_id' => $transaction->id,
                    'notes' => 'Refund: ' . $request->reason,
                ]);
            }

            // Create refund record
            $refund = \App\Models\Refund::create([
                'transaction_id' => $transaction->id,
                'refund_amount' => $refundAmount,
                'refund_reason' => $request->reason,
                'refunded_items' => $refundedItems,
                'refunded_by' => $request->user()->id ?? null,
            ]);

            // Update transaction status
            $isPartialRefund = $request->items && count($request->items) < $transaction->products->count();
            $transaction->update([
                'status' => $isPartialRefund ? 'partially_refunded' : 'refunded'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transaction refunded successfully',
                'refund' => $refund,
                'transaction' => $transaction->fresh()->load(['products', 'customer']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Refund failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'products']);
        
        $txArray = $transaction->toArray();
        
        // Transform products into items array
        $txArray['items'] = $transaction->products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $product->pivot->quantity,
                'unit_price' => $product->pivot->unit_price,
                'discount' => $product->pivot->discount,
                'tax' => $product->pivot->tax,
                'line_total' => $product->pivot->line_total,
            ];
        })->toArray();
        
        // Ensure meta is an object, not null
        if ($txArray['meta'] === null) {
            $txArray['meta'] = new \stdClass();
        }
        
        return response()->json($txArray);
    }
}
