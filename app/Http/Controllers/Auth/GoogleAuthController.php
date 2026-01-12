<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GoogleAccount;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
        $provider = Socialite::driver('google');
        return $provider
            ->stateless()
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->scopes([
                'https://www.googleapis.com/auth/gmail.readonly',
                'https://www.googleapis.com/auth/gmail.modify',
            ])
            ->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     */
    public function handleGoogleCallback()
    {
        try {
            /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
            $provider = Socialite::driver('google');
            $googleUser = $provider
                ->stateless()
                ->user();
            
            // Find or create the user by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create new user if doesn't exist
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(), // Google users are pre-verified
                ]);
            }

            // Find or create the Google account for this user
            $googleAccount = GoogleAccount::where('google_id', $googleUser->getId())->first();

            if ($googleAccount) {
                // Update existing Google account tokens
                $googleAccount->update([
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken ?? $googleAccount->google_refresh_token,
                ]);
            } else {
                // Check if this is the user's first Google account
                $isFirstAccount = $user->googleAccounts()->count() === 0;

                // Create new Google account
                $googleAccount = GoogleAccount::create([
                    'user_id' => $user->id,
                    'google_id' => $googleUser->getId(),
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'is_primary' => $isFirstAccount, // First account becomes primary
                ]);
            }

            // Log the user in
            Auth::login($user, true);

            return redirect()->intended('/dashboard');
            
        } catch (Exception $e) {
            return redirect('/login')->withErrors([
                'error' => 'Unable to authenticate with Google. Please try again.',
            ]);
        }
    }
}
