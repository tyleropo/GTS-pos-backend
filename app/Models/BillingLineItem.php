<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingLineItem extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'billing_statement_id',
        'type',
        'reference_id',
        'date',
        'description',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the billing statement that owns this line item.
     */
    public function billingStatement(): BelongsTo
    {
        return $this->belongsTo(BillingStatement::class);
    }

    /**
     * Scope for repair items.
     */
    public function scopeRepairType($query)
    {
        return $query->where('type', 'repair');
    }

    /**
     * Scope for product items.
     */
    public function scopeProductType($query)
    {
        return $query->where('type', 'product');
    }
}
