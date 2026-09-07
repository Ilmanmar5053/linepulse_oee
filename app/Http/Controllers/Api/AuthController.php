<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->email ?? $request->username ?? '');
        if (empty($loginInput)) {
            return response()->json([
                'success' => false,
                'message' => 'Username or email is required.',
            ], 422);
        }

        if (!str_contains($loginInput, '@')) {
            $loginInput = strtolower($loginInput) . '@prodcr.yasunaga.com';
        }

        $user = User::with('roles.permissions')->where('email', $loginInput)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials. Pastikan username dan password benar (default: "password").',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User account is deactivated.',
            ], 403);
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('display_name'),
                    'role_keys' => $user->roles->pluck('name'),
                    'permissions' => $user->roles->flatMap->permissions->pluck('name')->unique()->values(),
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            // Fallback for development if unauthenticated
            $user = User::with('roles.permissions')->first();
        } else {
            $user->load('roles.permissions');
        }

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('display_name'),
                    'role_keys' => $user->roles->pluck('name'),
                    'permissions' => $user->roles->flatMap->permissions->pluck('name')->unique()->values(),
                ],
            ],
        ]);
    }
}
