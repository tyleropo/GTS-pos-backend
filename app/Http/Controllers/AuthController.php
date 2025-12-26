<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController
{
    public function registerUser (Request $request) {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'confirmed'],
            'role' => 'sometimes|in:admin,manager,cashier,technician',
            'remember' => 'boolean',
        ]);

        try {
            $user = new User();
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->email = $validated['email'];
            $user->password = $validated['password'];
            $user->role = $validated['role'] ?? 'cashier';
            $user->save();
        } catch (\Throwable $error) {
            return response(['error' => 'Failed to register user', $error], 500);
        }

        $tokenExpiration = $request->remember ? null : now()->addDay();
        $token = $user->createToken($user->first_name . ' ' . $user->last_name, ['*'], $tokenExpiration);

        return response([
            'user' => $user,
            'token' => $token->plainTextToken,
            'tokenExpiration' => $tokenExpiration,
        ], 201);
    }

    public function loginUser (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['message' => "Incorrect credentials"],401);
        }

        // Update last login timestamp
        $user->last_login_at = now();
        $user->save();

        $tokenExpiration = $request->remember ? null : now()->addDay();
        $token = $user->createToken($user->first_name . ' ' . $user->last_name, ['*'], $tokenExpiration);

        return response([
            'user' => $user,
            'token' => $token->plainTextToken,
            'tokenExpiration' => $tokenExpiration,
        ], 200);
    }

    public function logoutUser(Request $request) {
        $request->user()->tokens()->delete();
        return response(204);
    }

    public function refresh(Request $request) {
        $request->user()->tokens()->delete();
        $token = $request->user()->createToken($request->user()->name, ['*'], now()->addDay());

        return response(201)
        ->withCookie('auth__token', $token->plainTextToken, null, '/', null, false, true);
    }


    public function getCurrentUser(Request $request) {
        return response($request->user(), 200);
    }

    public function updateUserPassword(Request $request) {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed']
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return response(204);
    }
}
