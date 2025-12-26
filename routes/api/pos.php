<?php

use App\Http\Controllers\Api\{
    ProductController,
    CategoryController,
    CustomerController,
    TransactionController,
    PurchaseOrderController,
    RepairController,
    DashboardController,
    LowStockController,
    BarcodeLookupController,
};
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('metrics', [DashboardController::class, 'metrics']);
        Route::get('recent-activity', [DashboardController::class, 'recentActivity']);
        Route::get('low-stock', [DashboardController::class, 'lowStock']);
        Route::get('top-selling', [DashboardController::class, 'topSelling']);
        Route::get('pending-repairs', [DashboardController::class, 'pendingRepairs']);
    });

    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/low-stock', LowStockController::class);
        Route::get('/barcode/{code}', BarcodeLookupController::class);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
    });

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{transaction}', [TransactionController::class, 'show']);
    });

    // Purchase Orders
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);

    // Repairs
    Route::apiResource('repairs', RepairController::class);
});
