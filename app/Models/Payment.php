<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'purchase_order_id',
        'reference_number',
        'amount',
        'payment_method',
        'date_received',
        'is_deposited',
        'date_deposited',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date_received' => 'date',
        'date_deposited' => 'date',
        'is_deposited' => 'boolean',
    ];

    /**
     * Get the purchase order that owns the payment.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
