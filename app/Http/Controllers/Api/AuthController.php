<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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

        // Support direct email, name, or legacy domain fallback
        $user = User::with('roles.permissions')
            ->where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        if (!$user && !str_contains($loginInput, '@')) {
            $user = User::with('roles.permissions')
                ->where('email', strtolower($loginInput) . '@prodcr.yasunaga.com')
                ->first();
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            AuditLog::record(
                action: 'LOGIN_FAILED',
                module: 'Keamanan & Autentikasi',
                description: "Percobaan login gagal dengan input identifier: '{$loginInput}'",
                oldValue: null,
                newValue: ['input' => $loginInput, 'ip' => $request->ip()],
                recordId: null,
                severity: 'warning',
                status: 'FAILED'
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials. Pastikan email/username dan password benar.',
            ], 401);
        }

        if (!$user->is_active) {
            AuditLog::record(
                action: 'LOGIN_BLOCKED',
                module: 'Keamanan & Autentikasi',
                description: "Percobaan login ditolak: Akun pengguna {$user->name} ({$user->email}) sedang dinonaktifkan",
                oldValue: null,
                newValue: ['user_id' => $user->id, 'email' => $user->email],
                recordId: $user->id,
                severity: 'danger',
                status: 'BLOCKED'
            );

            return response()->json([
                'success' => false,
                'message' => 'User account is deactivated.',
            ], 403);
        }

        Auth::login($user);

        $roleName = $user->roles->first()?->display_name ?? 'Operator';
        AuditLog::record(
            action: 'LOGIN',
            module: 'Keamanan & Autentikasi',
            description: "Autentikasi Berhasil: Sesi pengguna {$user->name} ({$roleName}) masuk ke sistem",
            oldValue: null,
            newValue: [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray()
            ],
            recordId: $user->id,
            severity: 'info',
            status: 'SUCCESS'
        );

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
        $user = Auth::user();
        if ($user) {
            AuditLog::record(
                action: 'LOGOUT',
                module: 'Keamanan & Autentikasi',
                description: "Pengguna {$user->name} keluar dari sistem (Logout)",
                oldValue: ['user_id' => $user->id, 'email' => $user->email],
                newValue: null,
                recordId: $user->id,
                severity: 'info',
                status: 'SUCCESS'
            );
        }

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
