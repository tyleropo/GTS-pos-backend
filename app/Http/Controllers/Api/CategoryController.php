<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()
            ->when($request->boolean('root_only'), fn ($q) => $q->whereNull('parent_id'))
            ->when($request->boolean('with_children'), fn ($q) => $q->with('children'))
            ->withCount('products');

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:categories'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }
}
