<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryProducts extends Model
{
    protected $fillable = [
        'inventory_product',
        'stock_keeping_unit',
        'stocks',
        'inventory_supplier',
    ];

    public function inventory_product() {
        return $this->belongsTo(Product::class, 'inventory_product');
    }

    public function inventory_supplier() {
        return $this->belongsTo(Supplier::class, 'inventory_supplier');
    }
}
