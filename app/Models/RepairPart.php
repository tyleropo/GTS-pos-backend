<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPart extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'repair_id',
        'product_id',
        'part_name',
        'part_number',
        'quantity',
        'unit_cost',
        'line_total',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the repair that owns this part.
     */
    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    /**
     * Get the associated product (if from inventory).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
