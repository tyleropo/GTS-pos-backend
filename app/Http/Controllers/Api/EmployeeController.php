<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->with('user')->orderBy('first_name')->get();

        return response()->json($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,terminated',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'sometimes|required|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'sometimes|required|in:active,inactive,terminated',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id,' . $id,
        ]);

        $employee->update($validated);

        return response()->json($employee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(null, 204);
    }

    /**
     * Create an employee from a user.
     */
    public function createFromUser(Request $request, string $userId)
    {
        $user = User::findOrFail($userId);
        
        // Check if user already has an employee record
        $existingEmployee = Employee::where('user_id', $userId)->first();
        if ($existingEmployee) {
            return response()->json(['message' => 'User already has an employee record'], 409);
        }

        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'nullable|date',
        ]);

        $employee = Employee::create([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => null,
            'position' => $validated['position'],
            'department' => $validated['department'] ?? null,
            'salary' => $validated['salary'],
            'hire_date' => $validated['hire_date'] ?? now()->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        return response()->json($employee, 201);
    }
}
