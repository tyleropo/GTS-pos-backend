<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product this movement belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made this movement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reference model (polymorphic relationship)
     * Could be Transaction, PurchaseOrder, InventoryAdjustment, etc.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if movement is stock in
     */
    public function isStockIn(): bool
    {
        return in_array($this->type, ['in', 'return']);
    }

    /**
     * Check if movement is stock out
     */
    public function isStockOut(): bool
    {
        return in_array($this->type, ['out', 'damage']);
    }

    /**
     * Check if movement is an adjustment
     */
    public function isAdjustment(): bool
    {
        return $this->type === 'adjustment';
    }

    /**
     * Scope to filter by movement type
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
