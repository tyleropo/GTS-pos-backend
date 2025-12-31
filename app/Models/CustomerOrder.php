<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'co_number',
        'customer_id',
        'status',
        'payment_status',
        'expected_at',
        'subtotal',
        'tax',
        'total',
        'notes',
        'items',
        'meta',
    ];

    protected $casts = [
        'expected_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'items' => 'array',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer for this order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all products in this customer order
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'customer_order_product')
            ->withPivot('quantity_ordered', 'quantity_fulfilled', 'unit_cost', 'tax', 'line_total', 'description')
            ->withTimestamps();
    }

    /**
     * Get all payments for this customer order
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Check if order is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if order has been submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if order has been fulfilled
     */
    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order is overdue (past expected date and not fulfilled/cancelled)
     */
    public function isOverdue(): bool
    {
        return $this->expected_at && 
               $this->expected_at->isPast() && 
               !$this->isFulfilled() && 
               !$this->isCancelled();
    }

    /**
     * Check if order is partially fulfilled
     */
    public function isPartiallyFulfilled(): bool
    {
        $totalOrdered = $this->products()->sum('customer_order_product.quantity_ordered');
        $totalFulfilled = $this->products()->sum('customer_order_product.quantity_fulfilled');
        
        return $totalFulfilled > 0 && $totalFulfilled < $totalOrdered;
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get overdue customer orders
     */
    public function scopeOverdue($query)
    {
        return $query->where('expected_at', '<', now())
            ->whereNotIn('status', ['fulfilled', 'cancelled']);
    }

    /**
     * Scope to get pending customer orders (submitted but not fulfilled)
     */
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }
}
