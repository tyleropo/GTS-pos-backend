<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'type',
        'status',
        'total_spent',
        'orders',
        'last_purchase',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_spent' => 'decimal:2',
        'orders' => 'integer',
        'last_purchase' => 'date',
    ];

    /**
     * Get all transactions for this customer.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all repairs for this customer.
     */
    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Get all billing statements for this customer.
     */
    public function billingStatements(): HasMany
    {
        return $this->hasMany(BillingStatement::class);
    }

    /**
     * Scope to get only active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope to get VIP customers.
     */
    public function scopeVip($query)
    {
        return $query->where('type', 'VIP');
    }
}
