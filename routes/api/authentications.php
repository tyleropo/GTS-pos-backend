<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->group(function () {
  Route::post('/register', 'registerUser');
  Route::post('/login', 'loginUser');
});

Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function () {
    Route::get('/me', 'getCurrentUser');
    Route::post('/logout', 'logoutUser');
    Route::post('/refresh', 'refresh');
    Route::put('/update-password', 'updateUserPassword');
});