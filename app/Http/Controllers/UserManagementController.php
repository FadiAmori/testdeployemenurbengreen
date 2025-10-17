<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('dashboard.pages.laravel-examples.user-management', compact('users'));
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:150',
            'role' => ['nullable', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'password' => 'nullable|string|min:6|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $attributes['profile_photo'] = $path;
        }

        $attributes['is_blocked'] = false;
        $plainPassword = $attributes['password'] ?? 'user123';
        $attributes['password'] = Hash::make($plainPassword);
        $attributes['role'] = $attributes['role'] ?? User::ROLE_USER;

        $user = User::create($attributes);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    public function edit(User $user)
    {
        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:150',
            'role' => ['nullable', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->user()->id === $user->id && ($attributes['role'] ?? $user->role) !== User::ROLE_ADMIN) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot downgrade your own role.',
            ], 422);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->raw_profile_photo) {
                Storage::disk('public')->delete($user->raw_profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $attributes['profile_photo'] = $path;
        }

        $attributes['role'] = $attributes['role'] ?? $user->role ?? User::ROLE_USER;

        $user->update($attributes);
        $user->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own account from this panel.',
            ], 422);
        }

        if ($user->raw_profile_photo) {
            Storage::disk('public')->delete($user->raw_profile_photo);
        }
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }

    public function block(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot block your own account.',
            ], 422);
        }

        try {
            $user->update(['is_blocked' => true]);

            // Invalidate all sessions for the blocked user
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User blocked successfully.',
                'user' => $this->formatUser($user->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Block user error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to block user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function unblock(Request $request, User $user)
    {
        try {
            $user->update(['is_blocked' => false]);
            return response()->json([
                'status' => 'success',
                'message' => 'User unblocked successfully.',
                'user' => $this->formatUser($user->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Unblock user error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to unblock user: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'prenom' => $user->prenom,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'about' => $user->about,
            'role' => $user->role ?? User::ROLE_USER,
            'is_blocked' => (bool) $user->is_blocked,
            'created_at' => optional($user->created_at)->format('d/m/y'),
            'profile_photo' => $user->profile_photo,
            'raw_profile_photo' => $user->raw_profile_photo,
        ];
    }
}
?>
