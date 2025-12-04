<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

// Categories
Route::apiResource('categories', CategoryController::class);

// Suppliers
Route::apiResource('suppliers', SupplierController::class);
