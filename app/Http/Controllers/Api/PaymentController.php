<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments with optional filters.
     */
    public function index(Request $request)
    {
        $payments = Payment::with(['payable' => function ($morphTo) {
            $morphTo->morphWith([
                \App\Models\PurchaseOrder::class => ['supplier'],
                \App\Models\CustomerOrder::class => ['customer'],
            ]);
        }])
            ->when($request->payable_id, fn ($q, $id) => $q->where('payable_id', $id))
            ->when($request->payable_type, fn ($q, $type) => $q->where('payable_type', $type))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->has('is_deposited'), fn ($q) => $q->where('is_deposited', $request->boolean('is_deposited')))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('date_received', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('date_received', '<=', $date))
            ->orderByDesc('date_received')
            ->paginate($request->integer('per_page', 15));

        return response()->json($payments);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payable_id' => ['required', 'uuid'],
            'payable_type' => ['required', 'string', 'in:purchase_order,customer_order,App\\Models\\PurchaseOrder,App\\Models\\CustomerOrder'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,cheque,bank_transfer,credit_card,online_wallet'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'date_received' => ['required', 'date'],
            'is_deposited' => ['nullable', 'boolean'],
            'date_deposited' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'is_consolidated' => ['nullable', 'boolean'],
            'related_orders' => ['nullable', 'array'],
            'related_orders.*.id' => ['required_with:related_orders', 'uuid'],
            'related_orders.*.type' => ['required_with:related_orders', 'string', 'in:purchase_order,customer_order'],
            'related_orders.*.amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        // Normalize payable_type and deduce type
        if (str_contains($validated['payable_type'], 'PurchaseOrder') || $validated['payable_type'] === 'purchase_order') {
            $validated['payable_type'] = 'App\Models\PurchaseOrder';
            $validated['type'] = 'outbound';
            
            // Verify existence
            if (!\App\Models\PurchaseOrder::where('id', $validated['payable_id'])->exists()) {
                 return response()->json(['message' => 'Invalid Purchase Order ID'], 422);
            }
        } else {
            $validated['payable_type'] = 'App\Models\CustomerOrder';
            $validated['type'] = 'inbound';
             
            // Verify existence
            if (!\App\Models\CustomerOrder::where('id', $validated['payable_id'])->exists()) {
                 return response()->json(['message' => 'Invalid Customer Order ID'], 422);
            }
        }

        // Ensure deposit date logic
        if (!($validated['is_deposited'] ?? false)) {
            $validated['date_deposited'] = null;
        }

        $payment = Payment::create($validated);
        
        // Update payment status for the orders
        if ($validated['is_consolidated'] ?? false) {
            // For consolidated payments, update all related orders
            if (!empty($validated['related_orders'])) {
                foreach ($validated['related_orders'] as $orderData) {
                    $orderId = $orderData['id'];
                    $orderType = $orderData['type'];
                    
                    if ($orderType === 'purchase_order') {
                        $order = \App\Models\PurchaseOrder::find($orderId);
                        if ($order) {
                            $order->payment_status = 'paid';
                            $order->save();
                        }
                    } elseif ($orderType === 'customer_order') {
                        $order = \App\Models\CustomerOrder::find($orderId);
                        if ($order) {
                            $order->payment_status = 'paid';
                            $order->save();
                        }
                    }
                }
            }
        } else {
            // For single payments, update the main payable order
            $payable = $payment->payable;
            if ($payable && method_exists($payable, 'setAttribute')) {
                $payable->payment_status = 'paid';
                $payable->save();
            }
        }
        
        return response()->json($payment->load('payable'), 201);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        return response()->json($payment->load('payable'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payable_id' => ['sometimes', 'uuid'],
            'payable_type' => ['sometimes', 'string', 'in:purchase_order,customer_order,App\Models\PurchaseOrder,App\Models\CustomerOrder'],
            'reference_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:cash,cheque,bank_transfer,credit_card,online_wallet'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'account_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_received' => ['sometimes', 'date'],
            'is_deposited' => ['sometimes', 'nullable', 'boolean'],
            'date_deposited' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Handle type normalization if changed (rare but possible)
        if (isset($validated['payable_type'])) {
             if (str_contains($validated['payable_type'], 'PurchaseOrder') || $validated['payable_type'] === 'purchase_order') {
                $validated['payable_type'] = 'App\Models\PurchaseOrder';
                $validated['type'] = 'outbound';
            } else {
                $validated['payable_type'] = 'App\Models\CustomerOrder';
                $validated['type'] = 'inbound';
            }
        }

        // Ensure deposit date logic
        if (isset($validated['is_deposited']) && !$validated['is_deposited']) {
            $validated['date_deposited'] = null;
        }

        $payment->update($validated);
        return response()->json($payment->fresh()->load('payable'));
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment)
    {
        // Identify all affected orders to revert their status
        $affectedOrders = [];

        if ($payment->is_consolidated && !empty($payment->related_orders)) {
            foreach ($payment->related_orders as $orderData) {
                $affectedOrders[] = [
                    'id' => $orderData['id'],
                    'type' => $orderData['type']
                ];
            }
        } else {
            // Non-consolidated, single order
            // Map full class name to simple type string if needed, or handle in loop
            $type = null;
            if ($payment->payable_type === 'App\Models\PurchaseOrder') {
                $type = 'purchase_order';
            } elseif ($payment->payable_type === 'App\Models\CustomerOrder') {
                $type = 'customer_order';
            }

            if ($type) {
                $affectedOrders[] = [
                    'id' => $payment->payable_id,
                    'type' => $type
                ];
            }
        }

        $payment->delete();

        // Revert statuses to 'pending'
        // Ideally we would check for other payments here, but for now we default to pending
        // so the user knows they need to verify payment status.
        foreach ($affectedOrders as $orderInfo) {
            $order = null;
            if ($orderInfo['type'] === 'purchase_order') {
                $order = \App\Models\PurchaseOrder::find($orderInfo['id']);
            } elseif ($orderInfo['type'] === 'customer_order') {
                $order = \App\Models\CustomerOrder::find($orderInfo['id']);
            }

            if ($order) {
                $order->payment_status = 'pending';
                $order->save();
            }
        }

        return response()->noContent();
    }
}
