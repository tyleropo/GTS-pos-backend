<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSerial extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'serial_number',
        'status',
        'transaction_id',
        'sold_date',
        'warranty_expiry',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sold_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    /**
     * Get the product that owns this serial number.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the transaction item for this serial.
     */
    public function transactionItem(): BelongsTo
    {
        return $this->belongsTo(TransactionItem::class, 'transaction_id');
    }

    /**
     * Scope to get available serials.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'in_stock');
    }

    /**
     * Scope to get sold serials.
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }
}
