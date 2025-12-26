<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    use HasUuids;

    protected $fillable = [
        'adjustment_number',
        'product_id',
        'type',
        'old_quantity',
        'new_quantity',
        'difference',
        'reason',
        'notes',
        'adjusted_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'difference' => 'integer',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product this adjustment belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who adjusted the inventory
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /**
     * Get the user who approved the adjustment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if adjustment is approved
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at) && !is_null($this->approved_by);
    }

    /**
     * Check if adjustment is pending approval
     */
    public function isPending(): bool
    {
        return !$this->isApproved();
    }

    /**
     * Check if adjustment increases stock
     */
    public function isIncrease(): bool
    {
        return $this->difference > 0;
    }

    /**
     * Check if adjustment decreases stock
     */
    public function isDecrease(): bool
    {
        return $this->difference < 0;
    }

    /**
     * Approve the adjustment
     */
    public function approve(int $userId): bool
    {
        $this->approved_by = $userId;
        $this->approved_at = now();
        
        return $this->save();
    }

    /**
     * Scope to get pending adjustments
     */
    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    /**
     * Scope to get approved adjustments
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Scope to filter by adjustment type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
