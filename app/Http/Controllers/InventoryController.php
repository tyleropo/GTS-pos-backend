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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
