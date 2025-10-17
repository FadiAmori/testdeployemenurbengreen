<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function create()
    {
        return view('dashboard.pages.profile');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $attributes = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:10',
            'about' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($user->raw_profile_photo) {
                Storage::disk('public')->delete($user->raw_profile_photo);
            }
            // Store new photo
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $attributes['profile_photo'] = $path;
        }

        $user->update($attributes);

        return back()->with('status', 'Profile successfully updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        // Allow changing password without requiring the current one (user might forget it)
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
        ])->save();

        return back()->with('status', 'Password updated successfully.');
    }
}
