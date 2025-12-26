<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
  Route::get('/products/categories', [App\Http\Controllers\Api\ProductController::class, 'getCategories']);
  Route::get('/products/low-stock', [App\Http\Controllers\Api\ProductController::class, 'getLowStock']);
  Route::post('/products/upload-image', [App\Http\Controllers\Api\ProductImageController::class, 'upload']);
  Route::delete('/products/delete-image', [App\Http\Controllers\Api\ProductImageController::class, 'delete']);
  Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
  
  Route::prefix('/product_')->group(function() {
    Route::get('/categories_brands', [ProductController::class, 'getCategoriesAndBrands']);
    Route::post('/brand', [ProductController::class, 'createBrand']);
  });

  Route::post('/products/categories', [App\Http\Controllers\Api\ProductController::class, 'createCategory']);
});

