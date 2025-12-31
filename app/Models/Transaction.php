<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'items',
        'meta',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'items' => 'array',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer for this transaction
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all products in this transaction
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_transaction')
            ->withPivot('quantity', 'unit_price', 'discount', 'tax', 'line_total')
            ->withTimestamps();
    }

    /**
     * Check if transaction was paid with cash
     */
    public function isPaidWithCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    /**
     * Check if transaction was paid with card
     */
    public function isPaidWithCard(): bool
    {
        return $this->payment_method === 'card';
    }

    /**
     * Check if transaction was paid with GCash
     */
    public function isPaidWithGCash(): bool
    {
        return $this->payment_method === 'gcash';
    }

    /**
     * Get the discount amount (subtotal + tax - total)
     */
    public function getDiscountAmountAttribute(): float
    {
        return ($this->subtotal + $this->tax) - $this->total;
    }

    /**
     * Get the number of items in the transaction
     */
    public function getItemCountAttribute(): int
    {
        return $this->products()->sum('product_transaction.quantity');
    }

    /**
     * Scope to filter by payment method
     */
    public function scopePaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->whereDate('created_at', '>=', $startDate);
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        return $query;
    }
}
