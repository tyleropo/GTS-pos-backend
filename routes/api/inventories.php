<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
  Route::apiResource('inventories', InventoryController::class);
});