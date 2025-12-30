<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function types()
    {
        // Get generic types that are often used but might not be in DB yet
        $defaultTypes = collect(['Regular', 'VIP']);
        
        // Get types from DB
        $dbTypes = Customer::select('type')
            ->distinct()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->pluck('type');

        // Merge and sort
        $allTypes = $defaultTypes->merge($dbTypes)->unique()->sort()->values();

        return response()->json($allTypes);
    }

    public function index(Request $request)
    {
        $query = Customer::query()
            ->when($request->search, function ($q, $term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('company', 'like', "%{$term}%");
            })
            ->withCount(['transactions', 'repairs'])
            ->withSum('transactions as total_spent', 'total');

        return response()->json(
            $query->orderBy('name')->orderBy('id')->paginate($request->integer('per_page', 25))
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
            'status' => ['nullable', 'string', 'in:Active,Inactive,Archived'],
            'type' => ['nullable', 'string'],
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
            'status' => ['nullable', 'string', 'in:Active,Inactive,Archived'],
            'type' => ['nullable', 'string'],
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
