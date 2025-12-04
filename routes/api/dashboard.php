<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('dashboard')->controller(DashboardController::class)->group(function () {
    Route::get('/metrics', 'metrics');
    Route::get('/recent-activity', 'recentActivity');
    Route::get('/low-stock', 'lowStock');
    Route::get('/top-selling', 'topSelling');
    Route::get('/pending-repairs', 'pendingRepairs');
});
