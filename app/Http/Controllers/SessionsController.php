<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        return view('dashboard.auth.sessions.create');
    }

public function store()
{
    $attributes = request()->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (!auth()->attempt($attributes)) {
        \Log::error('Login failed for email: ' . $attributes['email']);
        throw ValidationException::withMessages([
            'email' => 'Your provided credentials could not be verified.'
        ]);
    }

    $user = auth()->user();
    if ($user->is_blocked) {
        \Log::error('Blocked user attempted login: ' . $user->email);
        auth()->logout();
        throw ValidationException::withMessages([
            'email' => 'Your account is blocked. Please contact the administrator.'
        ]);
    }

    session()->regenerate();

    $target = $user->isAdmin() ? route('dashboard', [], false) : route('front.home', [], false);
    return redirect()->intended($target);
}

    public function show()
    {
        request()->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            request()->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

public function update()
{
    request()->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        request()->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password) // Use forceFill to bypass setPasswordAttribute mutator
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
}

    public function destroy()
    {
        auth()->logout();

        return redirect('/sign-in');
    }
}
?>
