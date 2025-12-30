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
        $payments = Payment::with('purchaseOrder.customer')
            ->when($request->purchase_order_id, fn ($q, $poId) => $q->where('purchase_order_id', $poId))
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
            'purchase_order_id' => ['required', 'uuid', 'exists:purchase_orders,id'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,cheque,bank_transfer,credit_card'],
            'date_received' => ['required', 'date'],
            'is_deposited' => ['boolean'],
            'date_deposited' => ['nullable', 'date', 'required_if:is_deposited,true'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure deposit date logic
        if (!($validated['is_deposited'] ?? false)) {
            $validated['date_deposited'] = null;
        }

        $payment = Payment::create($validated);
        return response()->json($payment->load('purchaseOrder.customer'), 201);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        return response()->json($payment->load('purchaseOrder.customer'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'purchase_order_id' => ['sometimes', 'uuid', 'exists:purchase_orders,id'],
            'reference_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:cash,cheque,bank_transfer,credit_card'],
            'date_received' => ['sometimes', 'date'],
            'is_deposited' => ['sometimes', 'boolean'],
            'date_deposited' => ['nullable', 'date', 'required_if:is_deposited,true'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure deposit date logic
        if (isset($validated['is_deposited']) && !$validated['is_deposited']) {
            $validated['date_deposited'] = null;
        }

        $payment->update($validated);
        return response()->json($payment->fresh()->load('purchaseOrder.customer'));
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->noContent();
    }
}
