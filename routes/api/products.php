<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
  Route::apiResource('products', ProductController::class);
  Route::post('/products/upload-image', [App\Http\Controllers\Api\ProductImageController::class, 'upload']);
  Route::delete('/products/delete-image', [App\Http\Controllers\Api\ProductImageController::class, 'delete']);
  Route::prefix('/product_')->controller(ProductController::class)->group(function() {
    Route::get('/categories_brands', 'getCategoriesAndBrands');
    Route::post('/category', 'createCategory');
    Route::post('/brand', 'createBrand');
  });
});

