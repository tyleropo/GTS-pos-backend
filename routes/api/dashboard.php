<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware('auth:sanctum')->controller(DashboardController::class)->group(function () {
    Route::get('/dashboard/metrics', 'getMetrics');
    Route::get('/dashboard/recent-activity', 'getRecentActivity');
    Route::get('/dashboard/low-stock', 'getLowStock');
    Route::get('/dashboard/top-selling', 'getTopSelling');
    Route::get('/dashboard/pending-repairs', 'getPendingRepairs');
});
