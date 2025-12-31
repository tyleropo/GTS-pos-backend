<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id',
        'payroll_period_id',
        'base_salary',
        'commission',
        'gross_pay',
        'total_deductions',
        'net_pay',
        'benefit_items',
        'deduction_items',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'float',
        'commission' => 'float',
        'gross_pay' => 'float',
        'total_deductions' => 'float',
        'net_pay' => 'float',
        'benefit_items' => 'array',
        'deduction_items' => 'array',
    ];

    /**
     * Get the user (employee) for this payroll record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Calculate and update gross pay
     */
    public function calculateGrossPay(): void
    {
        $benefitsTotal = collect($this->benefit_items)->sum('amount');
        $this->gross_pay = $this->base_salary + $this->commission + $benefitsTotal;
    }

    /**
     * Calculate and update total deductions
     */
    public function calculateTotalDeductions(): void
    {
        $this->total_deductions = collect($this->deduction_items)->sum('amount');
    }

    /**
     * Calculate and update net pay
     */
    public function calculateNetPay(): void
    {
        $this->net_pay = $this->gross_pay - $this->total_deductions;
    }

    /**
     * Recalculate all payroll amounts
     */
    public function recalculate(): void
    {
        $this->calculateGrossPay();
        $this->calculateTotalDeductions();
        $this->calculateNetPay();
    }
}
