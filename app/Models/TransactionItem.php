<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'serial_id',
        'quantity',
        'unit_price',
        'line_discount',
        'line_total',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_discount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the transaction that owns this item.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the serial number for this item (if serialized).
     */
    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'serial_id');
    }
}
