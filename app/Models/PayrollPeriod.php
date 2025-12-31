<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'name',
        'period_type',
        'employee_selection',
        'selected_user_ids',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'selected_user_ids' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * Get all payroll records for this period
     */
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    /**
     * Check if period is finalized
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['finalized', 'paid']);
    }

    /**
     * Check if period is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Get total payroll for this period
     */
    public function getTotalPayrollAttribute(): float
    {
        return $this->payrollRecords()->sum('net_pay');
    }
}
