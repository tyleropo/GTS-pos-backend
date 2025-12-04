<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'brand',
        'model',
        'cost_price',
        'selling_price',
        'markup_percentage',
        'tax_rate',
        'stock_quantity',
        'reorder_level',
        'max_stock_level',
        'unit_of_measure',
        'weight',
        'dimensions',
        'image_url',
        'is_active',
        'is_serialized',
        'warranty_period',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'max_stock_level' => 'integer',
        'warranty_period' => 'integer',
        'is_active' => 'boolean',
        'is_serialized' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier that owns the product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all serial numbers for this product.
     */
    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    /**
     * Get all transaction items for this product.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Get all purchase order items for this product.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get all inventory movements for this product.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Get available serial numbers (in stock).
     */
    public function availableSerials(): HasMany
    {
        return $this->serials()->where('status', 'in_stock');
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    /**
     * Get the profit margin attribute.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }
}
