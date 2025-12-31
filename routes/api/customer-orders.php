<?php

use App\Http\Controllers\Api\CustomerOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customer-orders', CustomerOrderController::class);
    Route::post('customer-orders/{customerOrder}/fulfill', [CustomerOrderController::class, 'fulfill']);
});
