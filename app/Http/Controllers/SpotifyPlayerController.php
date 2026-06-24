<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;

class SpotifyPlayerController extends Controller
{
    /**
     * Get a valid Spotify access token for the Web Playback SDK.
     */
    public function token(SpotifyService $spotifyService)
    {
        $user = Auth::user();

        if (!$user || !$user->spotify_token) {
            return response()->json(['error' => 'Not authenticated with Spotify'], 401);
        }

        // Test the token by making a lightweight request, e.g., to get the user's profile.
        // If it fails with 401, refresh the token.
        // Or simply refresh it proactively if it's near expiration (since we don't store expiration time, we just refresh it every time to be safe, or try and catch).
        
        $response = \Illuminate\Support\Facades\Http::withToken($user->spotify_token)
            ->get('https://api.spotify.com/v1/me');

        if ($response->status() === 401 && $user->spotify_refresh_token) {
            $newToken = $spotifyService->refreshUserToken($user);
            if ($newToken) {
                // Fetch profile with new token to update product status
                $newResponse = \Illuminate\Support\Facades\Http::withToken($newToken)
                    ->get('https://api.spotify.com/v1/me');
                if ($newResponse->successful()) {
                    $user->update(['spotify_product' => $newResponse->json('product')]);
                }
                return response()->json(['token' => $newToken]);
            } else {
                return response()->json(['error' => 'Failed to refresh token'], 401);
            }
        }

        if ($response->successful()) {
            $product = $response->json('product');
            if ($user->spotify_product !== $product) {
                $user->update(['spotify_product' => $product]);
            }
        }

        return response()->json(['token' => $user->spotify_token]);
    }
}
