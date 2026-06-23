<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider's authentication page.
     */
    public function redirect($provider)
    {
        // For Spotify, we need specific scopes to read user data and recently played (future proofing)
        if ($provider === 'spotify') {
            return Socialite::driver('spotify')
                ->stateless()
                ->scopes([
                    'user-read-email', 
                    'user-read-private', 
                    'user-read-recently-played',
                    'playlist-read-private',
                    'playlist-read-collaborative',
                    'playlist-modify-public',
                    'playlist-modify-private'
                ])
                ->with(['show_dialog' => 'true'])
                ->redirect();
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function callback(Request $request, $provider)
    {
        // Handle "Access Denied" or Cancel errors from provider
        if ($request->has('error') || $request->has('denied')) {
            $error = $request->get('error_description') ?? $request->get('error') ?? 'Access denied.';
            
            if (Auth::check()) {
                return redirect()->route('settings.index')->with('error', 'Failed to link ' . ucfirst($provider) . ': ' . $error);
            }
            return redirect()->route('login')->withErrors(['email' => 'Login canceled or failed: ' . $error]);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Logic for Logged-In Users (Linking Account)
            if (Auth::check()) {
                $currentUser = Auth::user();

                // Check if this social account is already linked to ANOTHER user
                $existingUser = User::where($provider . '_id', $socialUser->getId())
                                    ->where('id', '!=', $currentUser->id)
                                    ->first();

                if ($existingUser) {
                    return redirect()->route('settings.index')->with('error', 'This ' . ucfirst($provider) . ' account is already linked to another user.');
                }

                // Update current user details
                $updateData = [
                    $provider . '_id' => $socialUser->getId(),
                ];

                // Update tokens for Spotify
                if ($provider === 'spotify') {
                    $updateData['spotify_token'] = $socialUser->token;
                    $updateData['spotify_refresh_token'] = $socialUser->refreshToken;
                }

                // Verify email if it matches the social provider's email
                if ($currentUser->email === $socialUser->getEmail() && is_null($currentUser->email_verified_at)) {
                    $updateData['email_verified_at'] = now();
                }
                
                $currentUser->update($updateData);

                if ($provider === 'spotify') {
                    session()->forget('spotify_retry_count');
                }

                return redirect()->route('settings.index')->with('status', ucfirst($provider) . ' account connected successfully.');
            }

            // Logic for Guest Users (Login/Register)
            $user = $this->findOrCreateUser($socialUser, $provider);

            Auth::login($user, true);

            if ($provider === 'spotify') {
                session()->forget('spotify_retry_count');
            }

            return redirect()->route('dashboard');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            \Log::warning('Spotify OAuth InvalidStateException: ' . $e->getMessage());
            return $this->handleSpotifyCallbackError($provider, 'Invalid state. Please try again.');
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Spotify OAuth Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->handleSpotifyCallbackError($provider, 'Unable to login using ' . ucfirst($provider) . '. Please try again.');
        }
    }

    /**
     * Handle Spotify callback errors with a silent retry mechanism.
     */
    private function handleSpotifyCallbackError($provider, $defaultMsg)
    {
        if ($provider === 'spotify') {
            $retryCount = session()->get('spotify_retry_count', 0);
            if ($retryCount < 3) {
                session()->put('spotify_retry_count', $retryCount + 1);
                return response()->view('auth.spotify-retry', [
                    'retryCount' => $retryCount
                ]);
            }
            // Clear retry count if we gave up
            session()->forget('spotify_retry_count');
        }

        if (Auth::check()) {
            return redirect()->route('settings.index')->with('error', $defaultMsg);
        }
        return redirect()->route('login')->withErrors(['email' => $defaultMsg]);
    }

    /**
     * Find or create a user based on social provider data.
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        // 1. Check if user exists by Provider ID
        $user = User::where($provider . '_id', $socialUser->getId())->first();

        if ($user) {
            // Update tokens if likely changed (especially Spotify)
            if ($provider === 'spotify') {
                $user->update([
                    'spotify_token' => $socialUser->token,
                    'spotify_refresh_token' => $socialUser->refreshToken,
                ]);
            }
            return $user;
        }

        // 2. Check if user exists by Email (link accounts)
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link the provider to existing user
            $user->update([
                $provider . '_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(), // Optional: Update avatar
                'email_verified_at' => $user->email_verified_at ?? now(), // Mark as verified if not already
            ]);
            
            if ($provider === 'spotify') {
                $user->update([
                    'spotify_token' => $socialUser->token,
                    'spotify_refresh_token' => $socialUser->refreshToken,
                ]);
            }

            return $user;
        }

        // 3. Create new user
        $newUser = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => null, // Allow users to set this later
            'email_verified_at' => now(), // Assume verified by provider
            $provider . '_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);

        if ($provider === 'spotify') {
            $newUser->update([
                'spotify_token' => $socialUser->token,
                'spotify_refresh_token' => $socialUser->refreshToken,
            ]);
        }

        return $newUser;

    }

    /**
     * Unlink a social provider from the authenticated user's account.
     */
    public function unlink($provider)
    {
        $user = Auth::user();

        // Validate provider
        if (!in_array($provider, ['spotify', 'google'])) {
            return redirect()->route('settings.index')->with('error', 'Invalid provider.');
        }

        // Clear provider-specific fields
        $updateData = [
            $provider . '_id' => null,
        ];

        // For Spotify, also clear tokens
        if ($provider === 'spotify') {
            $updateData['spotify_token'] = null;
            $updateData['spotify_refresh_token'] = null;
        }

        $user->update($updateData);

        return redirect()->route('settings.index')->with('status', ucfirst($provider) . ' account disconnected successfully.');
    }
}
