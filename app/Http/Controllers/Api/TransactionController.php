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
            'payment_method' => ['required', 'in:cash,card,gcash'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
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
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
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

        return response()->json($transaction->load('customer', 'products'), 201);
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
