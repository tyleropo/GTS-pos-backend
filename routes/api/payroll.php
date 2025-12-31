<?php

use App\Http\Controllers\Api\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Payroll period management (admin/manager only)
    Route::get('/payroll/periods', [PayrollController::class, 'index']);
    Route::post('/payroll/periods', [PayrollController::class, 'store']);
    Route::get('/payroll/periods/{id}', [PayrollController::class, 'show']);
    Route::delete('/payroll/periods/{id}', [PayrollController::class, 'destroy']);
    
    // Payroll record management
    Route::put('/payroll/periods/{periodId}/records/{recordId}', [PayrollController::class, 'updateRecord']);
    
    // Payroll period actions
    Route::post('/payroll/periods/{id}/finalize', [PayrollController::class, 'finalize']);
    Route::post('/payroll/periods/{id}/mark-paid', [PayrollController::class, 'markAsPaid']);
    
    // Employee payroll history (accessible by employee themselves or admin/manager)
    Route::get('/payroll/employee/{userId}', [PayrollController::class, 'employeePayroll']);
});
