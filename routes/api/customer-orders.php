<?php

use App\Http\Controllers\Api\CustomerOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customer-orders', CustomerOrderController::class);
    Route::post('customer-orders/{customerOrder}/fulfill', [CustomerOrderController::class, 'fulfill']);
    Route::post('customer-orders/{customerOrder}/convert-to-cash', [CustomerOrderController::class, 'convertLineToCash']);
    Route::post('customer-orders/{customerOrder}/revert-to-cash', [CustomerOrderController::class, 'revertLineToCash']);
    Route::post('customer-orders/{customerOrder}/cancel', [CustomerOrderController::class, 'cancel']);
});
