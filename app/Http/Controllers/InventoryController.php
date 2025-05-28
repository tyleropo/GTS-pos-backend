<?php

namespace App\Http\Controllers;

use App\Models\InventoryProducts;
use Illuminate\Http\Request;

class InventoryController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $inventory_products = InventoryProducts::
            with(
                ['inventory_product', 
                'inventory_supplier',
                'inventory_product.product_category',
                'inventory_product.product_brand'
                ])
            ->get()
            ->map(function($inventory) {
                $inventory = $inventory->toArray();
                $inventory['product_category'] = $inventory['inventory_product']['product_category']['name'];
                $inventory['product_brand'] = $inventory['inventory_product']['product_brand']['name'];
                $inventory['inventory_product'] =  $inventory['inventory_product']['name'];
                $inventory['inventory_supplier'] = $inventory['inventory_supplier']['name'];
                return $inventory;
            });

        return response()->json($inventory_products);
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
