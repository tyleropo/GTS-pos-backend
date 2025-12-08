<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasUuids;

    protected $fillable = [
        'supplier_code',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all products from this supplier
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all purchase orders for this supplier
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the display name (company name or contact person)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?? $this->contact_person ?? 'Unknown Supplier';
    }
}
