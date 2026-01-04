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
            'payment_due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,submitted,received,cancelled'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string'], // Made nullable to allow new products
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
            'meta.new_products' => ['nullable', 'array'],
            'meta.new_products.*.name' => ['required_with:meta.new_products', 'string'],
            'meta.new_products.*.cost' => ['required_with:meta.new_products', 'numeric'],
        ]);

        $po = DB::transaction(function () use ($validated, $request) {
            $items = collect($validated['items']);
            $newProducts = collect($validated['meta']['new_products'] ?? []);
            
            // Create new products first (AS DRAFT)
            $createdProducts = [];
            foreach ($newProducts as $newProduct) {
                $product = Product::create([
                    'sku' => 'DRAFT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                    'name' => $newProduct['name'],
                    'cost_price' => $newProduct['cost'],
                    'selling_price' => $newProduct['cost'] * 1.2, // Default 20% markup
                    'stock_quantity' => 0, // Will be updated when received
                    'description' => $newProduct['description'] ?? null,
                    'supplier_id' => $validated['supplier_id'],
                    'status' => 'draft', // NEW: Mark as draft for review
                    'is_active' => false, // Inactive until approved
                    'reorder_level' => 5,
                    'markup_percentage' => 20,
                    'tax_rate' => 0,
                ]);
                $createdProducts[$newProduct['name']] = $product->id;
            }

            // Update items with newly created product IDs
            $items = $items->map(function ($item) use ($createdProducts) {
                // Check if this is a new product (ID starts with "new_")
                if (isset($item['product_id']) && str_starts_with($item['product_id'], 'new_')) {
                    // Extract product name from the ID (format: new_<ProductName>)
                    $productName = substr($item['product_id'], 4);
                    // Replace with actual created product ID
                    if (isset($createdProducts[$productName])) {
                        $item['product_id'] = $createdProducts[$productName];
                    }
                }
                return $item;
            });

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'supplier_id' => $validated['supplier_id'],
                'status' => $validated['status'] ?? 'draft',
                'payment_status' => $validated['payment_status'] ?? 'pending',
                'expected_at' => $validated['expected_at'] ?? null,
                'payment_due_date' => $validated['payment_due_date'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'items' => $items->toArray(),
                'meta' => array_merge($validated['meta'] ?? [], [
                    'auto_created_products' => $createdProducts,
                ]),
            ]);

            // Attach products
            foreach ($items as $item) {
                if ($item['product_id']) { // Only attach if we have a valid product ID
                    $po->products()->attach($item['product_id'], [
                        'quantity_ordered' => $item['quantity_ordered'],
                        'quantity_received' => 0,
                        'unit_cost' => $item['unit_cost'],
                        'tax' => $item['tax'] ?? 0,
                        'line_total' => $item['line_total'],
                        'description' => $item['description'] ?? null,
                    ]);
                }
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
            'payment_due_date' => 'nullable|date',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'nullable|string', // Made nullable to allow new products during update
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.line_total' => 'required|numeric|min:0', // Added for consistency
            'meta' => 'nullable|array',
            'meta.new_products' => 'nullable|array',
            'meta.new_products.*.name' => 'required_with:meta.new_products|string',
            'meta.new_products.*.cost' => 'required_with:meta.new_products|numeric',
        ]);


        $updatedPurchaseOrder = DB::transaction(function () use ($validated, $purchaseOrder) {
            $items = collect($validated['items']);
            $newProducts = collect($validated['meta']['new_products'] ?? []);
            
            // Create new products first (AS DRAFT)
            $createdProducts = [];
            foreach ($newProducts as $newProduct) {
                $product = Product::create([
                    'sku' => 'DRAFT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                    'name' => $newProduct['name'],
                    'cost_price' => $newProduct['cost'],
                    'selling_price' => $newProduct['cost'] * 1.2, // Default 20% markup
                    'stock_quantity' => 0, // Will be updated when received
                    'description' => $newProduct['description'] ?? null,
                    'supplier_id' => $validated['supplier_id'],
                    'status' => 'draft', // Mark as draft for review
                    'is_active' => false, // Inactive until approved
                    'reorder_level' => 5,
                    'markup_percentage' => 20,
                    'tax_rate' => 0,
                ]);
                $createdProducts[$newProduct['name']] = $product->id;
            }

            // Update items with newly created product IDs
            $items = $items->map(function ($item) use ($createdProducts) {
                // Check if this is a new product (ID starts with "new_")
                if (isset($item['product_id']) && str_starts_with($item['product_id'], 'new_')) {
                    // Extract product name from the ID (format: new_<ProductName>)
                    $productName = substr($item['product_id'], 4);
                    // Replace with actual created product ID
                    if (isset($createdProducts[$productName])) {
                        $item['product_id'] = $createdProducts[$productName];
                    }
                }
                return $item;
            });

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'] ?? 'pending',
                'expected_at' => $validated['expected_at'] ?? null,
                'payment_due_date' => $validated['payment_due_date'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'meta' => array_merge($validated['meta'] ?? [], [
                    'auto_created_products' => $createdProducts,
                ]),
            ]);

            $existingProducts = $purchaseOrder->products->keyBy('id');
            $syncData = [];
            foreach ($items as $item) {
                if ($item['product_id']) { // Only sync if we have a valid product ID
                    $existingPivot = $existingProducts->get($item['product_id'])?->pivot;
                    $syncData[$item['product_id']] = [
                        'quantity_ordered' => $item['quantity_ordered'],
                        'unit_cost' => $item['unit_cost'],
                        'line_total' => $item['line_total'] ?? ($item['quantity_ordered'] * $item['unit_cost']),
                        'description' => $item['description'] ?? null,
                        'quantity_received' => $existingPivot ? $existingPivot->quantity_received : 0,
                    ];
                }
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
            'approved_products' => ['nullable', 'array'],
            'approved_products.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'approved_products.*.selling_price' => ['required', 'numeric', 'min:0'],
            'approved_products.*.markup_percentage' => ['nullable', 'numeric'],
            'approved_products.*.brand' => ['nullable', 'string'],
            'approved_products.*.model' => ['nullable', 'string'],
            'approved_products.*.barcode' => ['nullable', 'string'],
            'approved_products.*.category_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated, $request) {
            // First, approve any draft products
            if (!empty($validated['approved_products'])) {
                foreach ($validated['approved_products'] as $approvedProduct) {
                    $product = Product::findOrFail($approvedProduct['product_id']);
                    
                    if ($product->status === 'draft') {
                        $product->update([
                            'status' => 'active',
                            'is_active' => true,
                            'sku' => str_replace('DRAFT-', 'SKU-', $product->sku),
                            'selling_price' => $approvedProduct['selling_price'],
                            'markup_percentage' => $approvedProduct['markup_percentage'] ?? $product->markup_percentage,
                            'brand' => $approvedProduct['brand'] ?? $product->brand,
                            'model' => $approvedProduct['model'] ?? $product->model,
                            'barcode' => $approvedProduct['barcode'] ?? $product->barcode,
                            'category_id' => $approvedProduct['category_id'] ?? $product->category_id,
                        ]);
                    }
                }
            }

            // Then proceed with receiving items
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantityReceived = $item['quantity_received'];

                // Auto-activate draft products when receiving stock - REMOVED
                // We want to keep them as draft so the user can review them in the inventory page
                /*
                if ($product->status === 'draft') {
                    $product->update([
                        'status' => 'active',
                        'is_active' => true,
                        'sku' => str_replace('DRAFT-', 'SKU-', $product->sku),
                    ]);
                }
                */

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
