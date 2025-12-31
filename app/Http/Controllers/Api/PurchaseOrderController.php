<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'products', 'payments'])
            ->when($request->status, fn ($q, $status) => $q->status($status))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        // Transform each purchase order to include items from products relationship
        $purchaseOrders->getCollection()->transform(function ($po) {
            $poArray = $po->toArray();
            
            // Replace items array with transformed products
            $poArray['items'] = $po->products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_ordered' => $product->pivot->quantity_ordered,
                    'quantity_received' => $product->pivot->quantity_received,
                    'unit_cost' => $product->pivot->unit_cost,
                    'tax' => $product->pivot->tax,
                    'line_total' => $product->pivot->line_total,
                    'description' => $product->pivot->description,
                ];
            })->toArray();
            
            // Ensure meta is an object, not null
            if ($poArray['meta'] === null) {
                $poArray['meta'] = new \stdClass();
            }
            
            return $poArray;
        });

        return response()->json($purchaseOrders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'uuid', 'exists:suppliers,id'],
            'expected_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,submitted,received,cancelled'],
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

        $po = DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'supplier_id' => $validated['supplier_id'],
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
                $po->products()->attach($item['product_id'], [
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'tax' => $item['tax'] ?? 0,
                    'line_total' => $item['line_total'],
                    'description' => $item['description'] ?? null,
                ]);
            }

            return $po;
        });

        return response()->json($po->load('supplier', 'products'), 201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'products', 'payments']);
        
        $poArray = $purchaseOrder->toArray();
        
        // Transform products into items array
        $poArray['items'] = $purchaseOrder->products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity_ordered' => $product->pivot->quantity_ordered,
                'quantity_received' => $product->pivot->quantity_received,
                'unit_cost' => $product->pivot->unit_cost,
                'tax' => $product->pivot->tax,
                'line_total' => $product->pivot->line_total,
                'description' => $product->pivot->description,
            ];
        })->toArray();
        
        // Ensure meta is an object, not null
        if ($poArray['meta'] === null) {
            $poArray['meta'] = new \stdClass();
        }
        
        return response()->json($poArray);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'status' => 'required|string|in:draft,submitted,received,cancelled',
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

        $updatedPurchaseOrder = DB::transaction(function () use ($validated, $purchaseOrder) {
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'] ?? 'pending',
                'expected_at' => $validated['expected_at'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'meta' => $validated['meta'] ?? [],
            ]);

            $existingProducts = $purchaseOrder->products->keyBy('id');
            $syncData = [];
            foreach ($validated['items'] as $item) {
                $existingPivot = $existingProducts->get($item['product_id'])?->pivot;
                $syncData[$item['product_id']] = [
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['quantity_ordered'] * $item['unit_cost'], // Recalculate or trust frontend? Trust frontend for now but ideally re-calc
                    'description' => $item['description'] ?? null,
                    'quantity_received' => $existingPivot ? $existingPivot->quantity_received : 0,
                ];
            }
            
            $purchaseOrder->products()->sync($syncData);

            return $purchaseOrder->fresh(['products', 'supplier']); // Return fresh supplier data
        });

        return response()->json($updatedPurchaseOrder);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated, $request) {
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantityReceived = $item['quantity_received'];

                // Update pivot table
                $purchaseOrder->products()->updateExistingPivot($item['product_id'], [
                    'quantity_received' => DB::raw("quantity_received + {$quantityReceived}"),
                ]);

                // Update product stock
                $oldStock = $product->stock_quantity;
                $product->increment('stock_quantity', $quantityReceived);
                $newStock = $product->fresh()->stock_quantity;

                // Log stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $quantityReceived,
                    'previous_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id,
                    'notes' => 'PO receipt: ' . $purchaseOrder->po_number,
                    'user_id' => $request->user()?->id,
                ]);
            }

            // Check if fully received
            $fullyReceived = $purchaseOrder->products()
                ->get()
                ->every(fn ($p) => $p->pivot->quantity_received >= $p->pivot->quantity_ordered);

            if ($fullyReceived) {
                $purchaseOrder->update(['status' => 'received']);
            }
        });

        return response()->json($purchaseOrder->fresh()->load('supplier', 'products'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft POs can be deleted'], 422);
        }

        $purchaseOrder->delete();
        return response()->noContent();
    }
}
