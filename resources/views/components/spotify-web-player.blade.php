@if(auth()->check() && auth()->user()->spotify_token && auth()->user()->isSpotifyPremium())
<div x-data="spotifyWebPlayer()" x-init="initPlayer()" class="fixed bottom-0 right-0 w-full md:w-auto p-4 z-50 pointer-events-none">
    <!-- Optional: Add a small floating player UI here if desired, otherwise we just use the SDK invisibly to stream audio -->
    <div x-show="isPlaying" class="bg-black/80 backdrop-blur-md border border-white/10 rounded-2xl p-4 flex items-center gap-4 shadow-2xl pointer-events-auto transform transition-all" x-transition>
        <img :src="currentTrack?.album?.images[0]?.url" class="w-12 h-12 rounded-lg shadow-md" alt="Album Art">
        <div class="flex-1 min-w-0 pr-4">
            <p class="text-white font-bold text-sm truncate" x-text="currentTrack?.name"></p>
            <p class="text-gray-400 text-xs truncate" x-text="currentTrack?.artists?.map(a => a.name).join(', ')"></p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="togglePlay" class="text-white hover:scale-110 transition-transform bg-white/10 p-2 rounded-full">
                <svg x-show="!isPaused" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25Zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25Z" clip-rule="evenodd" /></svg>
                <svg x-show="isPaused" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd" /></svg>
            </button>
        </div>
    </div>
</div>

<script src="https://sdk.scdn.co/spotify-player.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('spotifyWebPlayer', () => ({
            player: null,
            deviceId: null,
            isPlaying: false,
            isPaused: true,
            currentTrack: null,
            sdkInitialized: false,
            sdkReadyFired: false,
            initPlayer() {
                window._spotifyReady = false;
                window._pendingTrackUri = null;

                window.playSpotifyTrack = async (spotifyUri) => {
                    // Lazily connect on first real play action (user gesture)
                    if (!this.sdkInitialized) {
                        this.connectPlayer();
                        window._pendingTrackUri = spotifyUri;
                        return;
                    }
                    if (!window._spotifyReady || !this.deviceId) {
                        console.warn('Spotify player not ready yet — queuing track');
                        window._pendingTrackUri = spotifyUri;
                        return;
                    }
                    await this._doPlay(spotifyUri);
                };

                window.onSpotifyWebPlaybackSDKReady = () => {
                    this.sdkReadyFired = true;
                    // If play was clicked before SDK ready event, we connect now
                    if (window._pendingTrackUri) {
                        this.connectPlayer();
                    }
                };
            },
            connectPlayer() {
                if (this.sdkInitialized) return;
                this.sdkInitialized = true;

                const player = new Spotify.Player({
                    name: 'Reso Web Player',
                    getOAuthToken: cb => {
                        fetch('/spotify/token')
                            .then(response => response.json())
                            .then(data => {
                                if (data.token) {
                                    cb(data.token);
                                } else {
                                    console.error('Failed to get Spotify token:', data);
                                }
                            })
                            .catch(err => console.error('Token fetch error:', err));
                    },
                    volume: 0.5
                });

                this.player = player;

                player.addListener('initialization_error', ({ message }) => { console.error('init_error:', message); });
                player.addListener('authentication_error', ({ message }) => { console.error('auth_error:', message); });
                player.addListener('account_error', ({ message }) => { console.error('account_error:', message); });
                player.addListener('playback_error', ({ message }) => { console.error('playback_error:', message); });

                player.addListener('player_state_changed', state => {
                    if (!state) return;
                    this.currentTrack = state.track_window.current_track;
                    this.isPaused = state.paused;
                    this.isPlaying = true;
                });

                player.addListener('ready', ({ device_id }) => {
                    console.log('Spotify Web Playback SDK is Ready with Device ID', device_id);
                    this.deviceId = device_id;
                    window._spotifyReady = true;

                    // Flush any track that was clicked before we were ready
                    if (window._pendingTrackUri) {
                        const uri = window._pendingTrackUri;
                        window._pendingTrackUri = null;
                        this._doPlay(uri);
                    }
                });

                player.addListener('not_ready', ({ device_id }) => {
                    console.log('Device ID has gone offline', device_id);
                    window._spotifyReady = false;
                });

                player.connect();

                // Defensive polling fallback for Firefox state sync issues
                setInterval(() => {
                    if (this.player) {
                        this.player.getCurrentState().then(state => {
                            if (state) {
                                this.currentTrack = state.track_window.current_track;
                                this.isPaused = state.paused;
                                this.isPlaying = true;
                            }
                        }).catch(() => {});
                    }
                }, 1000);
            },
            async _doPlay(spotifyUri) {
                try {
                    const tokenRes = await fetch('/spotify/token');
                    const tokenData = await tokenRes.json();
                    if (!tokenData.token) throw new Error('No token returned');

                    const res = await fetch(`https://api.spotify.com/v1/me/player/play?device_id=${this.deviceId}`, {
                        method: 'PUT',
                        body: JSON.stringify({ uris: [spotifyUri] }),
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${tokenData.token}`
                        },
                    });

                    if (!res.ok) {
                        const errBody = await res.text();
                        console.error('Spotify play request failed:', res.status, errBody);
                    }
                } catch (err) {
                    console.error('Failed to play track:', err);
                }
            },
            togglePlay() {
                if (this.player) {
                    this.player.togglePlay();
                }
            }
        }));
    });
</script>
@endif
