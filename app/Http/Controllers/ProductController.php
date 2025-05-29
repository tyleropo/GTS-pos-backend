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

        $query = Product::query();
        
        if ($brand != 'All'){
            $query->where('brand', $brand);
        }
        if ($category != 'All') {
            $query->where('category', $category);
        }

        $products = $query
            ->where(function($querySearch) use ($search) {
                $querySearch
                ->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
                ->orWhere('specs', 'LIKE', "%$search%")
                ->orWhere('brand', 'LIKE', "%$search%")
                ->orWhere('category', 'LIKE', "%$search%");
            })->get()->map(function($product) {
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
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'string'],
            'price' => ['required', 'decimal:2'],
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
        $product = Product::where('id', $id)->firstOrFail();
        unset($product->created_at);
        unset($product->updated_at);
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
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'string'],
            'price' => ['required', 'decimal:2'],
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
