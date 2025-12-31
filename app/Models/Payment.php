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
        'payable_id',
        'payable_type',
        'type',
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
     * Get the owning payable model (PurchaseOrder or CustomerOrder).
     */
    public function payable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for inbound payments (from customers).
     */
    public function scopeInbound($query)
    {
        return $query->where('type', 'inbound');
    }

    /**
     * Scope for outbound payments (to suppliers).
     */
    public function scopeOutbound($query)
    {
        return $query->where('type', 'outbound');
    }
}
