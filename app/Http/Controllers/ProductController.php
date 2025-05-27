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
        $category = $request->query('category', null);
        $search = $request->query('search', null);
        $products = Product::with(['product_category', 'product_brand'])
            ->where('product_category', 'LIKE', "%$category%")
            ->where(function($query) use ($search) {
                $query
                ->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
                ->orWhere('specs', 'LIKE', "%$search%")
                ->orWhereHas('product_category', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->orWhereHas('product_brand', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                });
            })->get()->map(function($product) {
                $product = $product->toArray();
                $product['product_category'] =  $product['product_category']['name'];
                $product['product_brand'] = $product['product_brand']['name'];
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
            'product_category' => ['required', 'integer'],
            'product_brand' => ['required', 'integer'],
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
        $product = Product::with(['product_category', 'product_brand'])
            ->where('id', $id)
            ->firstOrFail();
        $product = $product->toArray();
        $product['category'] =  $product['product_category']['name'];
        $product['brand'] = $product['product_brand']['name'];
        unset($product['product_category'], $product['product_brand']);

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'product_category' => ['required', 'integer'],
            'product_brand' => ['required', 'integer'],
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

    public function getCategoriesAndBrands() {
        $categories = ProductCategory::pluck('name');
        $brands = ProductBrand::pluck('name');

       $data = [
            'categories' => $categories,
            'brands' => $brands
        ];
        
        return response()->json($data);
    }

    public function createCategory(Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string']
        ]);
        ProductCategory::create($validated);
        return response()->json([
            'message' => "Category name successfully added",
        ], 204);
    }

    public function createBrand(Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string']
        ]);
        ProductBrand::create($validated);
        return response()->json([
            'message' => "Brand name successfully added",
        ], 204);
    }

}
