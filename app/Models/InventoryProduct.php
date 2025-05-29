<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    protected $fillable = [
        'product_id',
        'stock_keeping_unit',
        'stocks',
        'supplier_id',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
