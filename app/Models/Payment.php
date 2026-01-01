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
        'status',
        'status_updated_at',
        'is_consolidated',
        'related_orders',
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
            // Auto-set status if not provided
            if (empty($payment->status)) {
                $payment->status = $payment->getDefaultStatus();
                $payment->status_updated_at = now();
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
        'is_consolidated' => 'boolean',
        'related_orders' => 'array',
        'status_updated_at' => 'datetime',
    ];

    /**
     * Append enriched related orders to model array
     */
    protected $appends = ['related_orders_details'];

    /**
     * Get enriched related orders details accessor
     */
    public function getRelatedOrdersDetailsAttribute()
    {
        return $this->getRelatedOrdersWithDetails();
    }

    /**
     * Get the owning payable model (PurchaseOrder or CustomerOrder).
     */
    public function payable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for inbound payments (receivables from customers).
     */
    public function scopeInbound($query)
    {
        return $query->where('type', 'inbound');
    }

    /**
     * Scope for outbound payments (payables to suppliers).
     */
    public function scopeOutbound($query)
    {
        return $query->where('type', 'outbound');
    }

    /**
     * Get default status based on payment type and method.
     */
    public function getDefaultStatus(): string
    {
        if ($this->type === 'inbound') {
            // Receivables
            return match($this->payment_method) {
                'check' => 'pending_deposit',
                'bank_transfer' => 'pending_verification',
                'gcash', 'paymaya' => 'pending_confirmation',
                'credit_card' => 'pending_settlement',
                default => 'received', // cash and others
            };
        } else {
            // Payables
            return match($this->payment_method) {
                'check' => 'issued',
                'bank_transfer' => 'pending_transfer',
                'gcash', 'paymaya' => 'pending_send',
                'credit_card' => 'pending_charge',
                default => 'paid', // cash and others
            };
        }
    }

    /**
     * Get status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_consolidated) {
            return 'Consolidated - ' . ucwords(str_replace('_', ' ', $this->status ?? 'unknown'));
        }
        return ucwords(str_replace('_', ' ', $this->status ?? 'unknown'));
    }

    /**
     * Get related orders with full details
     */
    public function getRelatedOrdersWithDetails()
    {
        if (!$this->is_consolidated || !$this->related_orders) {
            return [];
        }

        $orders = [];
        foreach ($this->related_orders as $orderData) {
            $orderId = $orderData['id'] ?? null;
            $orderType = $orderData['type'] ?? null;

            if (!$orderId || !$orderType) {
                continue;
            }

            if ($orderType === 'purchase_order') {
                $order = \App\Models\PurchaseOrder::with('supplier')->find($orderId);
                if ($order) {
                    $orders[] = [
                        'id' => $order->id,
                        'type' => 'purchase_order',
                        'number' => $order->po_number,
                        'date' => $order->created_at,
                        'supplier' => $order->supplier->company_name ?? $order->supplier->contact_person ?? 'Unknown',
                        'amount' => $orderData['amount'] ?? $order->total,
                        'total' => $order->total,
                    ];
                }
            } elseif ($orderType === 'customer_order') {
                $order = \App\Models\CustomerOrder::with('customer')->find($orderId);
                if ($order) {
                    $orders[] = [
                        'id' => $order->id,
                        'type' => 'customer_order',
                        'number' => $order->order_number,
                        'date' => $order->created_at,
                        'customer' => $order->customer->company ?? $order->customer->name ?? 'Unknown',
                        'amount' => $orderData['amount'] ?? $order->total,
                        'total' => $order->total,
                    ];
                }
            }
        }

        return $orders;
    }
}
