<?php

namespace App\Http\Controllers;

use App\Models\InventoryProduct;
use Illuminate\Http\Request;

class InventoryController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $inventories = InventoryProduct::with(['product', 'supplier'])
            ->get()
            ->map(function($inventory) {
                $inventory = $inventory->toArray();
                $inventory['category'] = $inventory['product']['category'];
                $inventory['brand'] = $inventory['product']['brand'];
                $inventory['product'] =  $inventory['product']['name'];
                $inventory['supplier'] = $inventory['supplier']['name'];
                unset($inventory['product_id'], $inventory['supplier_id']);
                return $inventory;
            });

        return response()->json($inventories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'stock_keeping_unit' => ['required', 'string'],
            'stocks' => ['requirede', 'integer'],
            'supplier_id' => ['required', 'integer'],
        ]);
        $inventory = InventoryProduct::create($validated);
        return response()->json([
            'message' => "Inventory product succesfully added",
            'product' => $inventory,
        ], 204);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $inventory = InventoryProduct::with(['product', 'supplier'])
           ->where('id', $id)
           ->firstOrFail();
        $inventory->product->makeHidden(['id', 'created_at', 'updated_at']);
        $inventory->supplier->makeHidden(['id', 'created_at', 'updated_at']);
        unset($inventory->product_id, $inventory->supplier_id);
        return response()->json($inventory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
