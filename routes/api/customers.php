<?php

use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/customers/types', [CustomerController::class, 'types']);
Route::apiResource('customers', CustomerController::class);