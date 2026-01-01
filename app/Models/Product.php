<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids, Auditable;

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
        'image_url',
        'is_active',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'max_stock_level' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category this product belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier for this product
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all stock movements for this product
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get all inventory adjustments for this product
     */
    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /**
     * Get all transactions that include this product
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'product_transaction')
            ->withPivot('quantity', 'unit_price', 'discount', 'tax', 'line_total')
            ->withTimestamps();
    }

    /**
     * Get all purchase orders that include this product
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'product_purchase_order')
            ->withPivot('quantity_ordered', 'quantity_received', 'unit_cost', 'tax', 'line_total')
            ->withTimestamps();
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Check if product is overstocked
     */
    public function isOverstocked(): bool
    {
        return $this->max_stock_level && $this->stock_quantity > $this->max_stock_level;
    }

    /**
     * Get the profit margin
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Get the profit amount per unit
     */
    public function getProfitPerUnitAttribute(): float
    {
        return $this->selling_price - $this->cost_price;
    }

    /**
     * Scope to get only active products (by is_active flag)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get products with active status
     */
    public function scopeStatusActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get draft products
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get discontinued products
     */
    public function scopeDiscontinued($query)
    {
        return $query->where('status', 'discontinued');
    }

    /**
     * Scope to get low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    /**
     * Scope to get out of stock products
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }
}
