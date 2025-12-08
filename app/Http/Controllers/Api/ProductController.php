<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier'])
            ->when($request->search, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%");
                });
            })
            ->when($request->category_id, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock());

        $products = $query->paginate($request->integer('per_page', 24));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $product = Product::create($validated);

        return response()->json($product->load(['category', 'supplier']), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'supplier', 'stockMovements']));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validatePayload($request, $product->id);
        
        // Track stock changes
        $oldStock = $product->stock_quantity;
        $product->update($validated);
        
        if (isset($validated['stock_quantity']) && $oldStock !== $validated['stock_quantity']) {
            $this->logStockMovement($product, $oldStock, $validated['stock_quantity']);
        }

        return response()->json($product->fresh()->load(['category', 'supplier']));
    }

    public function destroy(Product $product): Response
    {
        $product->delete();
        return response()->noContent();
    }

    protected function validatePayload(Request $request, ?string $productId = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $productId],
            'barcode' => ['nullable', 'string', 'max:150', 'unique:products,barcode,' . $productId],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'uuid', 'exists:suppliers,id'],
            'brand' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'markup_percentage' => ['nullable', 'numeric'],
            'tax_rate' => ['nullable', 'numeric'],
            'max_stock_level' => ['nullable', 'integer'],
            'unit_of_measure' => ['nullable', 'string'],
            'weight' => ['nullable', 'numeric'],
            'dimensions' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'is_active' => ['sometimes', 'boolean'],
            'is_serialized' => ['sometimes', 'boolean'],
            'warranty_period' => ['nullable', 'integer'],
        ]);
    }

    protected function logStockMovement(Product $product, int $oldStock, int $newStock): void
    {
        $product->stockMovements()->create([
            'type' => 'adjustment',
            'quantity' => $newStock - $oldStock,
            'previous_stock' => $oldStock,
            'new_stock' => $newStock,
            'notes' => 'Manual adjustment via product update',
            'user_id' => auth()->id(),
        ]);
    }
}
