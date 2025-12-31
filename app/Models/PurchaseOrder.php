<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'po_number',
        'supplier_id',
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
     * Get the supplier for this purchase order
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all products in this purchase order
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_purchase_order')
            ->withPivot('quantity_ordered', 'quantity_received', 'unit_cost', 'tax', 'line_total', 'description')
            ->withTimestamps();
    }

    /**
     * Get all payments for this purchase order
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Check if PO is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if PO has been submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if PO has been received
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    /**
     * Check if PO is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if PO is overdue (past expected date and not received/cancelled)
     */
    public function isOverdue(): bool
    {
        return $this->expected_at && 
               $this->expected_at->isPast() && 
               !$this->isReceived() && 
               !$this->isCancelled();
    }

    /**
     * Check if PO is partially received
     */
    public function isPartiallyReceived(): bool
    {
        $totalOrdered = $this->products()->sum('product_purchase_order.quantity_ordered');
        $totalReceived = $this->products()->sum('product_purchase_order.quantity_received');
        
        return $totalReceived > 0 && $totalReceived < $totalOrdered;
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get overdue purchase orders
     */
    public function scopeOverdue($query)
    {
        return $query->where('expected_at', '<', now())
            ->whereNotIn('status', ['received', 'cancelled']);
    }

    /**
     * Scope to get pending purchase orders (submitted but not received)
     */
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }
}
