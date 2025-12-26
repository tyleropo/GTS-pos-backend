<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $repairs = Repair::with('customer')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($repairs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'device' => ['required', 'string'],
            'serial_number' => ['nullable', 'string'],
            'issue_description' => ['required', 'string'],
            'promised_at' => ['nullable', 'date'],
        ]);

        $validated['ticket_number'] = 'REP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $validated['status'] = 'pending';

        $repair = Repair::create($validated);
        return response()->json($repair->load('customer'), 201);
    }

    public function show(Repair $repair)
    {
        return response()->json($repair->load('customer'));
    }

    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:pending,in_progress,completed,cancelled'],
            'resolution' => ['nullable', 'string'],
            'promised_at' => ['nullable', 'date'],
        ]);

        $repair->update($validated);
        return response()->json($repair->fresh()->load('customer'));
    }

    public function destroy(Repair $repair)
    {
        $repair->delete();
        return response()->noContent();
    }
}
