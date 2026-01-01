<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOrderAdjustment extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'customer_order_id',
        'type',
        'amount',
        'description',
        'related_product_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function customerOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    public function relatedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}
