<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
  Route::apiResource('products', ProductController::class);
  Route::prefix('/product_')->controller(ProductController::class)->group(function() {
    Route::get('/categories_brands', 'getCategoriesAndBrands');
    Route::post('/category', 'createCategory');
    Route::post('/brand', 'createBrand');
  });
});

