<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'product_category',
        'description',
        'specs',
        'price',
    ];

    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category');
    }
}
