<?php

use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport']);
    Route::get('/reports/customer', [ReportController::class, 'customerReport']);
    Route::get('/reports/government-markup', [ReportController::class, 'governmentMarkupReport']);
    Route::get('/reports/payment', [ReportController::class, 'paymentReport']);
    Route::post('/reports/export', [ReportController::class, 'exportReport']);
});
