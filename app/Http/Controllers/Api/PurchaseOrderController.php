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
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->when($request->status, fn ($q, $status) => $q->status($status))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($purchaseOrders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'uuid', 'exists:suppliers,id'],
            'expected_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'meta' => ['nullable', 'array'],
        ]);

        $po = DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'supplier_id' => $validated['supplier_id'],
                'status' => 'draft',
                'expected_at' => $validated['expected_at'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
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
                ]);
            }

            return $po;
        });

        return response()->json($po->load('supplier', 'products'), 201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json($purchaseOrder->load(['supplier', 'products']));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:draft,submitted,received,cancelled'],
            'expected_at' => ['nullable', 'date'],
        ]);

        $purchaseOrder->update($validated);
        return response()->json($purchaseOrder->fresh()->load('supplier'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated) {
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
                    'user_id' => auth()->id(),
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
