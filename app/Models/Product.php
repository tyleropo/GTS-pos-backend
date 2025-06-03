<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'brand',
        'image',
        'description',
        'stock_keeping_unit',
        'stocks',
        'barcode',
        'price',
        'supplier_id',
    ];
}
