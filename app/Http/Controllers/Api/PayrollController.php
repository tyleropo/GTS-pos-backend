<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Get all payroll periods
     */
    public function index(Request $request)
    {
        $query = PayrollPeriod::with(['payrollRecords.employee', 'payrollRecords.user'])->orderBy('start_date', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->get();

        // Add totals to each period
        $periods->each(function ($period) {
            $period->total_payroll = $period->payrollRecords->sum('net_pay');
            $period->employee_count = $period->payrollRecords->count();
        });

        return response()->json($periods);
    }

    /**
     * Create new payroll period
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'period_type' => ['required', 'in:weekly,bi-weekly,monthly,custom'],
            'employee_selection' => ['required', 'in:all,custom'],
            'selected_user_ids' => ['nullable', 'array'],
            'selected_user_ids.*' => ['integer', 'exists:employees,id'], // Changed from users to employees
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $period = PayrollPeriod::create($validated);

        // Get employees to include in payroll
        $query = \App\Models\Employee::where('status', 'active');

        // If custom selection, filter by selected employee IDs
        if ($validated['employee_selection'] === 'custom' && !empty($validated['selected_user_ids'])) {
            $query->whereIn('id', $validated['selected_user_ids']);
        }

        $employees = $query->get();
        
        // Create empty payroll records for selected employees
        foreach ($employees as $employee) {
            PayrollRecord::create([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id, // Keep user_id populated if available for backward compat
                'payroll_period_id' => $period->id,
                'base_salary' => $employee->salary, // Use employee's base salary
                'commission' => 0,
                'gross_pay' => $employee->salary, // Initial gross is base
                'total_deductions' => 0,
                'net_pay' => $employee->salary, // Initial net is base
            ]);
        }

        return response()->json($period->load('payrollRecords.employee'), 201);
    }

    /**
     * Get payroll period with all records
     */
    public function show($id)
    {
        $period = PayrollPeriod::with(['payrollRecords.employee', 'payrollRecords.user'])->findOrFail($id);
        
        $period->total_payroll = $period->payrollRecords->sum('net_pay');
        $period->employee_count = $period->payrollRecords->count();

        return response()->json($period);
    }

    /**
     * Update a payroll record
     */
    public function updateRecord(Request $request, $periodId, $recordId)
    {
        $record = PayrollRecord::where('payroll_period_id', $periodId)
            ->findOrFail($recordId);

        // Check if period is finalized
        if ($record->payrollPeriod->isFinalized()) {
            return response()->json([
                'message' => 'Cannot update finalized payroll period'
            ], 422);
        }

        $validated = $request->validate([
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'benefit_items' => ['nullable', 'array'],
            'benefit_items.*.name' => ['required', 'string'],
            'benefit_items.*.amount' => ['required', 'numeric', 'min:0'],
            'deduction_items' => ['nullable', 'array'],
            'deduction_items.*.name' => ['required', 'string'],
            'deduction_items.*.amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $record->update($validated);
        
        // Recalculate totals
        $record->recalculate();
        $record->save();

        return response()->json($record);
    }

    /**
     * Finalize payroll period (lock it)
     */
    public function finalize($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->isFinalized()) {
            return response()->json([
                'message' => 'Payroll period is already finalized'
            ], 422);
        }

        $period->update(['status' => 'finalized']);

        return response()->json($period);
    }

    /**
     * Mark payroll period as paid
     */
    public function markAsPaid($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->isFinalized()) {
            return response()->json([
                'message' => 'Payroll period must be finalized before marking as paid'
            ], 422);
        }

        $period->update(['status' => 'paid']);

        return response()->json($period);
    }

    /**
     * Get employee's payroll history
     */
    public function employeePayroll($userId)
    {
        $records = PayrollRecord::with('payrollPeriod')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($records);
    }

    /**
     * Delete payroll period (only if draft)
     */
    public function destroy($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->isFinalized()) {
            return response()->json([
                'message' => 'Cannot delete finalized payroll period'
            ], 422);
        }

        $period->delete();

        return response()->json(['message' => 'Payroll period deleted successfully']);
    }
}
