<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
        'movement_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'movement_date' => 'datetime',
    ];

    /**
     * Get the product for this movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made this movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for incoming movements (positive quantity).
     */
    public function scopeIncoming($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope for outgoing movements (negative quantity).
     */
    public function scopeOutgoing($query)
    {
        return $query->where('quantity', '<', 0);
    }

    /**
     * Scope by movement type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('movement_type', $type);
    }
}
