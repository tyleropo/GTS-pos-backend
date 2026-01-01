<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $repairs = Repair::with(['customer', 'products'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->customer_ids, function ($q, $ids) {
                $idList = is_array($ids) ? $ids : explode(',', $ids);
                $q->whereIn('customer_id', $idList);
            })
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($repairs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'device' => ['required', 'string'],
            'device_model' => ['nullable', 'string'],
            'serial_number' => ['nullable', 'string'],
            'issue_description' => ['required', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'technician' => ['nullable', 'string'],
            'promised_at' => ['nullable', 'date'],
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', 'uuid', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated) {
            $validated['ticket_number'] = 'REP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $validated['status'] = 'pending';
            $validated['cost'] = $validated['cost'] ?? 0;

            $repair = Repair::create(collect($validated)->except('products')->toArray());

            if (!empty($validated['products'])) {
                $this->syncProducts($repair, $validated['products']);
            }

            return response()->json($repair->load(['customer', 'products']), 201);
        });
    }

    public function show(Repair $repair)
    {
        return response()->json($repair->load(['customer', 'products']));
    }

    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'customer_id' => ['sometimes', 'nullable', 'uuid', 'exists:customers,id'],
            'device' => ['sometimes', 'string'],
            'device_model' => ['sometimes', 'nullable', 'string'],
            'serial_number' => ['sometimes', 'nullable', 'string'],
            'issue_description' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:pending,in_progress,completed,cancelled'],
            'resolution' => ['nullable', 'string'],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'technician' => ['sometimes', 'nullable', 'string'],
            'promised_at' => ['nullable', 'date'],
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', 'uuid', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $repair, $validated) {
            $repair->update(collect($validated)->except('products')->toArray());

            if ($request->has('products')) {
                $this->syncProducts($repair, $validated['products']);
            }

            return response()->json($repair->fresh()->load(['customer', 'products']));
        });
    }

    public function destroy(Repair $repair)
    {
        return DB::transaction(function () use ($repair) {
            // Restore stock for all attached products
            foreach ($repair->products as $product) {
                $product->increment('stock_quantity', $product->pivot->quantity);
            }
            
            $repair->delete();
            return response()->noContent();
        });
    }

    private function syncProducts(Repair $repair, array $productsData)
    {
        $currentProducts = $repair->products()->get()->keyBy('id');
        $newProducts = collect($productsData)->keyBy('id');
        
        // 1. Detect removed products and restore stock
        foreach ($currentProducts as $id => $product) {
            if (!$newProducts->has($id)) {
                $product->increment('stock_quantity', $product->pivot->quantity);
            }
        }
        
        $syncData = [];
        
        // 2. Process new/updated products
        foreach ($productsData as $data) {
            $id = $data['id'];
            $qty = $data['quantity'];
            $price = $data['unit_price'];
            
            $current = $currentProducts->get($id);
            $oldQty = $current ? $current->pivot->quantity : 0;
            $diff = $qty - $oldQty;
            
            if ($diff != 0) {
                $productModel = $current ?? Product::find($id);
                
                if ($diff > 0) {
                    // Deduct stock
                    if ($productModel->stock_quantity < $diff) {
                         abort(422, "Insufficient stock for product: {$productModel->name}");
                    }
                    $productModel->decrement('stock_quantity', $diff);
                } else {
                    // Restore stock (diff is negative, so abs(diff) or just subtract negative)
                    $productModel->increment('stock_quantity', abs($diff));
                }
            }
            
            $syncData[$id] = [
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $qty * $price
            ];
        }
        
        $repair->products()->sync($syncData);
    }
}
