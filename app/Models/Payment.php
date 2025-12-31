<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'payment_number',
        'payable_id',
        'payable_type',
        'type',
        'reference_number',
        'amount',
        'payment_method',
        'bank_name',
        'account_number',
        'date_received',
        'is_deposited',
        'date_deposited',
        'notes',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = static::generatePaymentNumber();
            }
        });
    }

    /**
     * Generate a unique payment number.
     * Format: PAY-YYYYMMDD-XXXX
     */
    public static function generatePaymentNumber()
    {
        $prefix = 'PAY-' . now()->format('Ymd') . '-';
        
        $latestPayment = static::where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('payment_number')
            ->first();

        if ($latestPayment) {
            $lastNumber = (int) substr($latestPayment->payment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'date_received' => 'date',
        'date_deposited' => 'date',
        'is_deposited' => 'boolean',
    ];

    /**
     * Get the owning payable model (PurchaseOrder or CustomerOrder).
     */
    public function payable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for inbound payments (from customers).
     */
    public function scopeInbound($query)
    {
        return $query->where('type', 'inbound');
    }

    /**
     * Scope for outbound payments (to suppliers).
     */
    public function scopeOutbound($query)
    {
        return $query->where('type', 'outbound');
    }
}
