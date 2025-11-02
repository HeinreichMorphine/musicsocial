<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\Request;

class SpotifySearchController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Search for tracks.
     */
    public function search(Request $request)
    {
        $query = $request->validate(['query' => 'required|string|min:3'])['query'];

        $tracks = $this->spotifyService->searchTracks($query);

        return response()->json($tracks);
    }
}