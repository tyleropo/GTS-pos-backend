<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        $brand = $request->query('brand', 'All');
        $category = $request->query('category', 'All');
        $search = $request->query('search', null);

        $query = Product::with('supplier');
        
        if ($brand != 'All'){
            $query->where('brand', $brand);
        }
        if ($category != 'All') {
            $query->where('category', $category);
        }

        $products = $query->where(function($querySearch) use ($search) {
                $querySearch
                ->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
                ->orWhere('brand', 'LIKE', "%$search%")
                ->orWhere('category', 'LIKE', "%$search%")
                ->orWhere('stock_keeping_unit', 'LIKE', "%$search%")
                ->orWhere('barcode', 'LIKE', "%$search%")
                ->orWhereHas('supplier', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                });
            })->get()->map(function($product) {
                $product->supplier->makeHidden(['id', 'created_at', 'updated_at']);
                unset($product->supplier_id);
                unset($product->created_at);
                unset($product->updated_at);
                return $product;
            });

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'string'],
            'brand' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'barcode' => ['nullable', 'string'],
            'stock_keeping_unit' => ['required', 'string'],
            'stocks' => ['required', 'integer'],
            'price' => ['required', 'decimal:2'],
            'supplier_id' => ['required', 'integer'],
        ]);
        $product = Product::create($validated);
        return response()->json([
            'message' => "Product succesfully added",
            'product' => $product,
        ], 204);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('supplier')->where('id', $id)->firstOrFail();
        $product->supplier->makeHidden(['id', 'created_at', 'updated_at']);
        unset($product->supplier_id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'string'],
            'brand' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'barcode' => ['nullable', 'string'],
            'stock_keeping_unit' => ['required', 'string'],
            'stocks' => ['required', 'integer'],
            'price' => ['required', 'decimal:2'],
            'supplier_id' => ['required', 'integer'],
        ]);
        $product = Product::where('id', $id)->firstOrFail();
        $product->update($validated);
        return response()->json([
            'message' => 'Product successfully updated!',
            'product' => $product
        ], 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getCategoriesAndBrands() 
    {
        $categories = ProductCategory::all();
        $brands = ProductBrand::all();

        $data = [
            'categories' => $categories,
            'brands' => $brands
        ];
        
        return response()->json($data);
    }

    public function createCategory(Request $request) 
    {
        $validated = $request->validate([
            'name' => ['required', 'string']
        ]);
        ProductCategory::create($validated);
        return response()->json([
            'message' => "Category name successfully added",
        ], 204);
    }

    public function createBrand(Request $request) 
    {
        $validated = $request->validate([
            'name' => ['required', 'string']
        ]);
        ProductBrand::create($validated);
        return response()->json([
            'message' => "Brand name successfully added",
        ], 204);
    }

}
