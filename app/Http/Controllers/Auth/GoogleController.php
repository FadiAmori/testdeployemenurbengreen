<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $config = config('services.google');
        $redirectUri = $config['redirect'] ?? route('oauth.google.callback', [], true);

        if (!($config['client_id'] ?? null) || !$redirectUri) {
            return $this->sendErrorRedirect(__('Google login is not configured. Please contact the administrator.'));
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_oauth_state');
        if (!$expectedState || $expectedState !== $request->input('state')) {
            return $this->sendErrorRedirect(__('Unable to verify Google login. Please try again.'));
        }

        if (!$request->has('code')) {
            return $this->sendErrorRedirect(__('Google login failed. Please try again.'));
        }

        $config = config('services.google');
        $redirectUri = $config['redirect'] ?? route('oauth.google.callback', [], true);
        if (!($config['client_id'] ?? null) || !($config['client_secret'] ?? null) || !$redirectUri) {
            return $this->sendErrorRedirect(__('Google login is not configured. Please contact the administrator.'));
        }

        try {
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $redirectUri,
                'code' => $request->input('code'),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Google OAuth token request failed.', ['exception' => $exception]);
            return $this->sendErrorRedirect(__('Unable to communicate with Google. Please try again.'));
        }

        if ($tokenResponse->failed()) {
            Log::warning('Google OAuth token response error.', ['response' => $tokenResponse->json()]);
            return $this->sendErrorRedirect(__('Unable to verify Google login. Please try again.'));
        }

        $tokenData = $tokenResponse->json();
        if (!isset($tokenData['access_token'])) {
            return $this->sendErrorRedirect(__('Unable to retrieve Google authentication details.'));
        }

        try {
            $googleUserResponse = Http::withToken($tokenData['access_token'])
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');
        } catch (\Throwable $exception) {
            Log::error('Google OAuth user info request failed.', ['exception' => $exception]);
            return $this->sendErrorRedirect(__('Unable to communicate with Google. Please try again.'));
        }

        if ($googleUserResponse->failed()) {
            Log::warning('Google OAuth user info response error.', ['response' => $googleUserResponse->json()]);
            return $this->sendErrorRedirect(__('Unable to retrieve Google profile information.'));
        }

        $googleUser = $googleUserResponse->json();
        $email = $googleUser['email'] ?? null;

        if (!$email) {
            return $this->sendErrorRedirect(__('Your Google account does not have a verified email address.'));
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser['name'] ?? ($googleUser['given_name'] ?? 'Google User'),
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'is_blocked' => false,
                'role' => User::ROLE_USER,
            ]);
            if (!empty($googleUser['email_verified'])) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        } else {
            if ($user->is_blocked) {
                return $this->sendErrorRedirect(__('Your account is blocked. Please contact the administrator.'));
            }
            $attributesToUpdate = [];
            if (!$user->name && isset($googleUser['name'])) {
                $attributesToUpdate['name'] = $googleUser['name'];
            }
            if (!empty($googleUser['email_verified']) && !$user->email_verified_at) {
                $attributesToUpdate['email_verified_at'] = now();
            }
            if (!empty($attributesToUpdate)) {
                $user->forceFill($attributesToUpdate)->save();
            }
        }

        Auth::login($user, true);

        $target = $user->isAdmin() ? route('dashboard', [], false) : route('front.home', [], false);
        return redirect()->intended($target);
    }

    protected function sendErrorRedirect(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['google' => $message]);
    }
}
?>
