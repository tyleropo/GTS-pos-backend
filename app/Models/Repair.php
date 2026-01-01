<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repair extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'device',
        'device_model',
        'serial_number',
        'status',
        'issue_description',
        'resolution',
        'cost',
        'technician',
        'promised_at',
    ];

    protected $casts = [
        'promised_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer who owns this repair ticket
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if repair is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if repair is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if repair is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if repair is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if repair is overdue (past promised date)
     */
    public function isOverdue(): bool
    {
        return $this->promised_at && 
               $this->promised_at->isPast() && 
               !$this->isCompleted() && 
               !$this->isCancelled();
    }

    /**
     * Scope to get pending repairs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get in-progress repairs
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get overdue repairs
     */
    public function scopeOverdue($query)
    {
        return $query->where('promised_at', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }
    /**
     * Get the products (parts) used in this repair
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_repair')
            ->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }
}
