<?php

use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::post('suppliers', [SupplierController::class, 'store']);
});
