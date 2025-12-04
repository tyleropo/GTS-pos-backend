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
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'confirmed'],
            'remember' => 'boolean',
        ]);

        try {
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = $validated['password'];
            $user->save();
        } catch (\Throwable $error) {
            return response(['error' => 'Failed to register user', $error], 500);
        }

        $tokenExpiration = $request->remember ? null : now()->addDay();
        $token = $user->createToken($user->name, ['*'], $tokenExpiration);

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

        $tokenExpiration = $request->remember ? null : now()->addDay();
        $token = $user->createToken($user->name, ['*'], $tokenExpiration);

        // Transform user to match frontend schema
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => explode(' ', $user->name)[0] ?? 'Admin',
            'last_name' => explode(' ', $user->name)[1] ?? 'User',
            'role' => 'admin', // Default role since User model doesn't have role field yet
            'is_active' => true,
            'last_login_at' => $user->updated_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];

        return response([
            'user' => $userData,
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


    public function me(Request $request) {
        $user = $request->user();
        
        // Transform user to match frontend schema
        return response([
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => explode(' ', $user->name)[0] ?? 'Admin',
            'last_name' => explode(' ', $user->name)[1] ?? 'User',
            'role' => 'admin', // Default role since User model doesn't have role field yet
            'is_active' => true,
            'last_login_at' => $user->updated_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ], 200);
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
