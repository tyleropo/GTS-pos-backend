<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repair extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'device',
        'device_model',
        'issue',
        'status',
        'cost_estimate',
        'final_cost',
        'technician_id',
        'repair_date',
        'completion_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'repair_date' => 'date',
        'completion_date' => 'date',
        'cost_estimate' => 'decimal:2',
        'final_cost' => 'decimal:2',
    ];

    /**
     * Get the customer for this repair.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the technician (user) for this repair.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get all parts used in this repair.
     */
    public function parts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    /**
     * Scope for completed repairs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    /**
     * Scope for in-progress repairs.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'In Progress');
    }

    /**
     * Scope for pending repairs.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['Diagnostic', 'Waiting for Parts']);
    }
}
