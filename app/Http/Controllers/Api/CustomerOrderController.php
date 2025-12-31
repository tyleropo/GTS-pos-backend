<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $customerOrders = CustomerOrder::with(['customer', 'products', 'payments'])
            ->when($request->status, fn ($q, $status) => $q->status($status))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        // Transform each customer order to include items from products relationship
        $customerOrders->getCollection()->transform(function ($co) {
            $coArray = $co->toArray();
            
            // Replace items array with transformed products
            $coArray['items'] = $co->products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_ordered' => $product->pivot->quantity_ordered,
                    'quantity_fulfilled' => $product->pivot->quantity_fulfilled,
                    'unit_cost' => $product->pivot->unit_cost,
                    'tax' => $product->pivot->tax,
                    'line_total' => $product->pivot->line_total,
                    'description' => $product->pivot->description,
                ];
            })->toArray();
            
            // Calculate payment totals
            $totalPaid = $co->payments->sum('amount');
            $outstanding = max(0, $co->total - $totalPaid);
            
            // Determine payment status
            if ($totalPaid == 0) {
                $paymentStatus = 'pending';
            } elseif ($totalPaid >= $co->total) {
                $paymentStatus = 'paid';
            } else {
                $paymentStatus = 'partial';
            }
            
            $coArray['payment_status'] = $paymentStatus;
            $coArray['total_paid'] = round($totalPaid, 2);
            $coArray['outstanding_balance'] = round($outstanding, 2);
            
            // Ensure meta is an object, not null
            if ($coArray['meta'] === null) {
                $coArray['meta'] = new \stdClass();
            }
            
            return $coArray;
        });

        return response()->json($customerOrders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'expected_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,submitted,fulfilled,cancelled'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        $co = DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);

            $co = CustomerOrder::create([
                'co_number' => 'CO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_id' => $validated['customer_id'],
                'status' => $validated['status'] ?? 'draft',
                'payment_status' => $validated['payment_status'] ?? 'pending',
                'expected_at' => $validated['expected_at'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'items' => $items->toArray(),
                'meta' => $validated['meta'] ?? [],
            ]);

            // Attach products
            foreach ($items as $item) {
                $co->products()->attach($item['product_id'], [
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_fulfilled' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'tax' => $item['tax'] ?? 0,
                    'line_total' => $item['line_total'],
                    'description' => $item['description'] ?? null,
                ]);
            }

            return $co;
        });

        return response()->json($co->load('customer', 'products'), 201);
    }

    public function show(CustomerOrder $customerOrder)
    {
        $customerOrder->load(['customer', 'products', 'payments']);
        
        $coArray = $customerOrder->toArray();
        
        // Transform products into items array
        $coArray['items'] = $customerOrder->products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity_ordered' => $product->pivot->quantity_ordered,
                'quantity_fulfilled' => $product->pivot->quantity_fulfilled,
                'unit_cost' => $product->pivot->unit_cost,
                'tax' => $product->pivot->tax,
                'line_total' => $product->pivot->line_total,
                'description' => $product->pivot->description,
            ];
        })->toArray();
        
        // Calculate payment totals
        $totalPaid = $customerOrder->payments->sum('amount');
        $outstanding = max(0, $customerOrder->total - $totalPaid);
        
        // Determine payment status
        if ($totalPaid == 0) {
            $paymentStatus = 'pending';
        } elseif ($totalPaid >= $customerOrder->total) {
            $paymentStatus = 'paid';
        } else {
            $paymentStatus = 'partial';
        }
        
        $coArray['payment_status'] = $paymentStatus;
        $coArray['total_paid'] = round($totalPaid, 2);
        $coArray['outstanding_balance'] = round($outstanding, 2);
        
        // Ensure meta is an object, not null
        if ($coArray['meta'] === null) {
            $coArray['meta'] = new \stdClass();
        }
        
        return response()->json($coArray);
    }

    public function update(Request $request, CustomerOrder $customerOrder)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|string|in:draft,submitted,fulfilled,cancelled',
            'payment_status' => 'nullable|string|in:pending,paid',
            'expected_at' => 'nullable|date',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $updatedCustomerOrder = DB::transaction(function () use ($validated, $customerOrder) {
            $customerOrder->update([
                'customer_id' => $validated['customer_id'],
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'] ?? 'pending',
                'expected_at' => $validated['expected_at'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'meta' => $validated['meta'] ?? [],
            ]);

            $existingProducts = $customerOrder->products->keyBy('id');
            $syncData = [];
            foreach ($validated['items'] as $item) {
                $existingPivot = $existingProducts->get($item['product_id'])?->pivot;
                $syncData[$item['product_id']] = [
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['quantity_ordered'] * $item['unit_cost'],
                    'description' => $item['description'] ?? null,
                    'quantity_fulfilled' => $existingPivot ? $existingPivot->quantity_fulfilled : 0,
                ];
            }
            
            $customerOrder->products()->sync($syncData);

            return $customerOrder->fresh(['products', 'customer']);
        });

        return response()->json($updatedCustomerOrder);
    }

    public function fulfill(Request $request, CustomerOrder $customerOrder)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity_fulfilled' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($customerOrder, $validated, $request) {
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantityFulfilled = $item['quantity_fulfilled'];

                // Update pivot table
                $customerOrder->products()->updateExistingPivot($item['product_id'], [
                    'quantity_fulfilled' => DB::raw("quantity_fulfilled + {$quantityFulfilled}"),
                ]);

                // DEDUCT product stock (this is the key difference from purchase orders)
                $oldStock = $product->stock_quantity;
                $product->decrement('stock_quantity', $quantityFulfilled);
                $newStock = $product->fresh()->stock_quantity;

                // Log stock movement as 'out'
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $quantityFulfilled,
                    'previous_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'reference_type' => CustomerOrder::class,
                    'reference_id' => $customerOrder->id,
                    'notes' => 'Customer order fulfillment: ' . $customerOrder->co_number,
                    'user_id' => $request->user()?->id,
                ]);
            }

            // Check if fully fulfilled
            $fullyFulfilled = $customerOrder->products()
                ->get()
                ->every(fn ($p) => $p->pivot->quantity_fulfilled >= $p->pivot->quantity_ordered);

            if ($fullyFulfilled) {
                $customerOrder->update(['status' => 'fulfilled']);
            }
        });

        return response()->json($customerOrder->fresh()->load('customer', 'products'));
    }

    public function destroy(CustomerOrder $customerOrder)
    {
        if ($customerOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft customer orders can be deleted'], 422);
        }

        $customerOrder->delete();
        return response()->noContent();
    }
}
