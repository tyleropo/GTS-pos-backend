<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all transactions for this customer
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all repair tickets for this customer
     */
    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Get total amount spent by customer
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->transactions()->sum('total');
    }

    /**
     * Get the number of transactions
     */
    public function getTransactionCountAttribute(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Check if customer is a business customer
     */
    public function isBusinessCustomer(): bool
    {
        return !is_null($this->company);
    }

    /**
     * Get display name (name or company)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company ?? $this->name;
    }
}
