<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Import the Hash facade
use App\Models\User;

class RegisterController extends Controller
{
    public function create()
    {
        return view('dashboard.auth.register.create');
    }

    public function store()
    {
        $attributes = request()->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:5|max:255',
        ]);

        $attributes['password'] = Hash::make($attributes['password']); // Use Hash::make correctly

        $user = User::create($attributes);
        auth()->login($user);
        
        return redirect('/dashboard');
    }
}