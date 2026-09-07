<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        $users = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function roles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Role::withCount('users')->get(),
        ]);
    }

    public function generateDefaultUsers(): JsonResponse
    {
        $usersList = [
            ['name' => 'Super Administrator', 'email' => 'superadmin@prodcr.yasunaga.com', 'role' => 'super_admin'],
            ['name' => 'System Admin', 'email' => 'admin@prodcr.yasunaga.com', 'role' => 'admin'],
            ['name' => 'Plant Manager', 'email' => 'manager@prodcr.yasunaga.com', 'role' => 'production_manager'],
            ['name' => 'Production Supervisor', 'email' => 'supervisor@prodcr.yasunaga.com', 'role' => 'production_supervisor'],
            ['name' => 'Alim Utama', 'email' => 'alim.utama@prodcr.yasunaga.com', 'role' => 'production_supervisor'],
            ['name' => 'Agus Setiawan', 'email' => 'agus.setiawan@prodcr.yasunaga.com', 'role' => 'production_leader'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@prodcr.yasunaga.com', 'role' => 'production_leader'],
            ['name' => 'Bambang Haryanto', 'email' => 'bambang.haryanto@prodcr.yasunaga.com', 'role' => 'production_leader'],
            ['name' => 'Quality Control Inspector', 'email' => 'qc@prodcr.yasunaga.com', 'role' => 'quality_control'],
            ['name' => 'Maintenance Engineer', 'email' => 'maintenance@prodcr.yasunaga.com', 'role' => 'maintenance'],
            ['name' => 'Lead Operator', 'email' => 'operator@prodcr.yasunaga.com', 'role' => 'operator'],
        ];

        $createdCount = 0;
        foreach ($usersList as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            $role = Role::where('name', $item['role'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
            $createdCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil men-generate {$createdCount} master data akun user dengan domain @prodcr.yasunaga.com dan default password 'password'.",
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->roles()->sync($validated['role_ids']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user->load('roles'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:6',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active = $validated['is_active'] ?? $user->is_active;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->roles()->sync($validated['role_ids']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $user->load('roles'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own active user account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
