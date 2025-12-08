<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()
            ->when($request->search, function ($q, $term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('company', 'like', "%{$term}%");
            })
            ->withCount(['transactions', 'repairs']);

        return response()->json(
            $query->orderBy('name')->paginate($request->integer('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email', 'unique:customers'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'company' => ['nullable', 'string'],
        ]);

        $customer = Customer::create($validated);
        return response()->json($customer, 201);
    }

    public function show(Customer $customer)
    {
        return response()->json($customer->load(['transactions', 'repairs']));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email', 'unique:customers,email,' . $customer->id],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'company' => ['nullable', 'string'],
        ]);

        $customer->update($validated);
        return response()->json($customer->fresh());
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->noContent();
    }
}
