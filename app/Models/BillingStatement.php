<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingStatement extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'period_start',
        'period_end',
        'repair_subtotal',
        'product_subtotal',
        'grand_total',
        'generated_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_date' => 'date',
        'repair_subtotal' => 'decimal:2',
        'product_subtotal' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    /**
     * Get the customer for this billing statement.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all line items for this billing statement.
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(BillingLineItem::class);
    }

    /**
     * Get repair line items.
     */
    public function repairItems(): HasMany
    {
        return $this->lineItems()->where('type', 'repair');
    }

    /**
     * Get product line items.
     */
    public function productItems(): HasMany
    {
        return $this->lineItems()->where('type', 'product');
    }

    /**
     * Scope for draft statements.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    /**
     * Scope for sent statements.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'Sent');
    }

    /**
     * Scope for paid statements.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'Paid');
    }
}
