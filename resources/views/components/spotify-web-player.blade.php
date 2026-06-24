@if(auth()->check())
    @php 
        $hasToken = !empty(auth()->user()->spotify_token);
        $isPremiumUser = $hasToken && auth()->user()->isSpotifyPremium(); 
    @endphp

    @persist('global-spotify-player')
        <div x-data="spotifyWebPlayer({ isPremium: {{ $isPremiumUser ? 'true' : 'false' }} })" 
             x-show="playerVisible"
             class="fixed bottom-0 left-0 right-0 md:left-auto md:right-4 md:bottom-4 md:w-96 z-50 pointer-events-none"
             style="display:none;"
             x-transition>
             
            <div id="spotify-safe-house" style="display:none;"></div>

            <div class="bg-white dark:bg-black backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl p-4 shadow-2xl pointer-events-auto transition-colors duration-200">
                
                <div class="flex items-center gap-4">
                    <img :src="albumArt || '/images/default-album-art.png'" class="w-12 h-12 rounded-lg shadow-md shrink-0" alt="Album Art" :class="isLoading ? 'opacity-50 animate-pulse' : ''">
                    <div class="flex-1 min-w-0">
                        <p class="text-slate-900 dark:text-white font-bold text-sm truncate transition-colors" 
                           x-text="isLoading ? 'Connecting to Spotify...' : (trackName || 'Select a track')"></p>
                        <p class="text-slate-500 dark:text-zinc-400 text-xs truncate transition-colors" 
                           x-text="isLoading ? 'Waking up device' : (artistName || '')"></p>
                    </div>

                    <button @click="collapsed = !collapsed" class="text-slate-400 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-white p-1 transition-all" :class="collapsed ? '' : 'rotate-180'" title="Collapse/Expand">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </button>

                    <button @click="closePlayer()" class="text-slate-400 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-white p-1 transition-colors" title="Close player">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                    </button>
                </div>

                <div x-show="!collapsed" x-transition>

                    <template x-if="!isPremium">
                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-2 mt-2 text-[11px] text-amber-600 dark:text-amber-400 flex items-center justify-between">
                            <span x-text="noPreview ? 'No preview available (Spotify licensing)' : 'Playing 30s preview (Free Account)'"></span>
                            <a href="https://www.spotify.com/premium/" target="_blank" class="underline font-bold hover:opacity-80 shrink-0 ml-2">Upgrade</a>
                        </div>
                    </template>

                    <div class="mt-3">
                        <div class="relative h-1.5 bg-slate-200 dark:bg-white/20 rounded-full cursor-pointer group" @click="seekTo($event)">
                            <div class="absolute top-0 left-0 h-full bg-slate-800 dark:bg-white rounded-full transition-all" :style="`width: ${progressPercent}%`"></div>
                            <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-slate-800 dark:bg-white rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity" :style="`left: calc(${progressPercent}% - 6px)`"></div>
                        </div>
                        <div class="flex justify-between mt-1 text-[11px] text-slate-500 dark:text-zinc-400">
                            <span x-text="formatTime(positionMs)"></span>
                            <span x-text="formatTime(durationMs)"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-6 mt-3">
                        <button @click="seekRelative(-10000)" class="text-slate-700 hover:text-black dark:text-zinc-300 dark:hover:text-white hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                        </button>
                        
                        <button @click="togglePlay()" 
                                :disabled="isLoading || (noPreview && !isPremium)"
                                class="text-white bg-slate-900 hover:bg-black dark:text-black dark:bg-white dark:hover:bg-zinc-200 hover:scale-110 transition-transform rounded-full p-3 flex items-center justify-center" 
                                :class="isLoading || (noPreview && !isPremium) ? 'opacity-50 cursor-not-allowed hover:scale-100' : ''">
                            
                            <svg x-show="isLoading" class="animate-spin w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="!isLoading && isPaused" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd"/></svg>
                            <svg x-show="!isLoading && !isPaused" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25Zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25Z" clip-rule="evenodd"/></svg>
                        </button>

                        <button @click="seekRelative(10000)" class="text-slate-700 hover:text-black dark:text-zinc-300 dark:hover:text-white hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" style="transform: scaleX(-1)"><path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <script>
    @verbatim
    window.isSpotifySupported = true;
    if (!window.__spotifyNativeAudio) {
        window.__spotifyNativeAudio = new Audio();
    }

    // Guard: only register the Alpine component once across wire:navigate page transitions.
    // The <script> is inside @persist so it re-executes on every navigation; without this
    // guard Alpine would silently re-register, re-run init(), and overwrite
    // window.onSpotifyWebPlaybackSDKReady — producing a second disconnected player instance.
    if (!window.__spotifyPlayerRegistered) {
        window.__spotifyPlayerRegistered = true;
        document.addEventListener('alpine:init', () => {
            Alpine.data('spotifyWebPlayer', (config) => ({
                isPremium: config.isPremium,
                player: null,
                deviceId: null,
                deviceReady: false,
                isPlaying: false,
                isPaused: true,
                playerVisible: false,
                collapsed: false,
                isLoading: false,
                positionMs: 0,
                durationMs: 0,
                progressInterval: null,
                noPreview: false,
                trackName: null,
                artistName: null,
                albumArt: null,
                pendingTrackUri: null,
                _retryTimer: null,

                get progressPercent() {
                    return this.durationMs > 0 ? (this.positionMs / this.durationMs) * 100 : 0;
                },

                formatTime(ms) {
                    if (!ms) return '0:00';
                    const totalSec = Math.floor(ms / 1000);
                    const min = Math.floor(totalSec / 60);
                    const sec = totalSec % 60;
                    return `${min}:${sec.toString().padStart(2, '0')}`;
                },

                init() {
                    console.log('[SpotifyPlayer] init() called — player:', this.player ? 'EXISTS' : 'null', '| deviceId:', this.deviceId, '| deviceReady:', this.deviceReady, '| isPremium:', this.isPremium);

                    // Guard against stacking duplicate audio listeners if init() ever runs again
                    if (!window.__spotifyAudioListenersAttached) {
                        window.__spotifyAudioListenersAttached = true;
                        window.__spotifyNativeAudio.addEventListener('timeupdate', () => {
                            if (!this.isPremium) this.positionMs = window.__spotifyNativeAudio.currentTime * 1000;
                        });
                        window.__spotifyNativeAudio.addEventListener('ended', () => {
                            if (!this.isPremium) {
                                this.isPaused = true;
                                this.positionMs = 0;
                            }
                        });
                    }

                    // Global trigger function
                    window.toggleSpotifyPlayer = (spotifyUri, meta) => {
                        this.playerVisible = true;
                        this.collapsed = false;
                        this.noPreview = false;
                        this.isLoading = true;

                        if (meta) {
                            this.trackName = meta.name || null;
                            this.artistName = meta.artist || null;
                            this.albumArt = meta.art || null;
                        }

                        this._doPlay(spotifyUri, meta);
                    };

                    if (this.isPremium) {
                        window.onSpotifyWebPlaybackSDKReady = () => this.connectPlayer();
                        if (typeof Spotify !== 'undefined' && typeof Spotify.Player !== 'undefined') {
                            this.connectPlayer();
                        }
                    }

                    if (this.isPremium && this.player && !this.isPaused) this.startPolling();
                },

                startPolling() {
                    this.stopPolling();
                    this.progressInterval = setInterval(() => {
                        if (this.isPremium && this.player && !this.isPaused) {
                            this.player.getCurrentState().then(state => {
                                if (state) {
                                    this.positionMs = state.position;
                                    this.durationMs = state.duration;
                                    this.isPaused = state.paused;
                                }
                            }).catch(() => {});
                        }
                    }, 500);
                },

                stopPolling() {
                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }
                },

                connectPlayer() {
                    // Skip only if we have a live, confirmed connection.
                    // If player exists but device is dead (deviceReady: false), disconnect the
                    // ghost and fall through to reconnect.
                    if (this.player && this.deviceReady) return;
                    if (this.player && !this.deviceReady) {
                        try { this.player.disconnect(); } catch (e) {}
                        this.player = null;
                        this.deviceId = null;
                    }

                    const player = new Spotify.Player({
                        name: 'Reso Web Player',
                        getOAuthToken: cb => {
                            fetch('/spotify/token').then(res => res.json()).then(data => { if (data.token) cb(data.token); }).catch(err => console.error(err));
                        },
                        volume: 0.5
                    });

                    player.addListener('player_state_changed', state => {
                        if (!state) return;
                        this.isPaused = state.paused;
                        this.positionMs = state.position;
                        this.durationMs = state.duration;
                        if (!this.isPaused) this.startPolling();
                        else this.stopPolling();
                    });

                    player.addListener('ready', ({ device_id }) => {
                        this.deviceId = device_id;
                        this.player = player;

                        setTimeout(() => {
                            this.deviceReady = true;
                            
                            // Set global variable and broadcast event
                            window.isSpotifyReady = true;
                            window.dispatchEvent(new Event('spotify-ready'));

                            if (this.pendingTrackUri) {
                                const uri = this.pendingTrackUri;
                                this.pendingTrackUri = null;
                                this._doPlay(uri);
                            }
                        }, 2000);
                    });

                    player.addListener('not_ready', () => { 
                        this.deviceReady = false;
                        this.deviceId = null;
                        this.player = null;   // must null so connectPlayer() doesn't bail at its guard

                        // Broadcast offline status
                        window.isSpotifyReady = false;
                        window.dispatchEvent(new Event('spotify-not-ready'));
                    });

                    player.addListener('initialization_error', ({ message }) => {
                        console.error('Spotify SDK failed to initialize (DRM or connection blocked):', message);
                        this.deviceReady = false;
                        window.isSpotifyReady = false;
                        window.dispatchEvent(new Event('spotify-not-ready'));
                        window.isSpotifySupported = false;
                        window.dispatchEvent(new Event('spotify-unsupported'));
                    });

                    player.addListener('authentication_error', ({ message }) => {
                        console.error('Spotify SDK authentication failed:', message);
                        this.deviceReady = false;
                        window.isSpotifyReady = false;
                        window.dispatchEvent(new Event('spotify-not-ready'));
                        window.isSpotifySupported = false;
                        window.dispatchEvent(new Event('spotify-unsupported'));
                    });

                    player.addListener('account_error', ({ message }) => {
                        console.error('Spotify SDK account restrictions (Free account or region limits):', message);
                        this.deviceReady = false;
                        window.isSpotifyReady = false;
                        window.dispatchEvent(new Event('spotify-not-ready'));
                        window.isSpotifySupported = false;
                        window.dispatchEvent(new Event('spotify-unsupported'));
                    });

                    player.addListener('playback_error', ({ message }) => {
                        console.error('Spotify SDK playback error:', message);
                    });
                    
                    // Safe-House DOM Interceptor — permanent, never torn down.
                    // Ensures the SDK's hidden iframe always lands inside #spotify-safe-house
                    // (inside the @persist boundary) regardless of network speed or navigation timing.
                    const originalBodyAppend = document.body.appendChild;
                    const originalBodyInsertBefore = document.body.insertBefore;
                    
                    document.body.appendChild = function(element) {
                        if (element && element.tagName === 'IFRAME' && (element.src.includes('sdk.scdn.co') || element.src.includes('spotify'))) {
                            document.getElementById('spotify-safe-house').appendChild(element);
                            return element;
                        }
                        return originalBodyAppend.apply(this, arguments);
                    };
                    
                    document.body.insertBefore = function(element, reference) {
                        if (element && element.tagName === 'IFRAME' && (element.src.includes('sdk.scdn.co') || element.src.includes('spotify'))) {
                            document.getElementById('spotify-safe-house').appendChild(element);
                            return element;
                        }
                        return originalBodyInsertBefore.apply(this, arguments);
                    };

                    player.connect();
                },

                async _doPlay(spotifyUri, meta, retryCount = 0) {
                    // Cancel any in-flight retry chain before starting a new attempt
                    clearTimeout(this._retryTimer);

                    if (this.isPremium) {
                        if (!this.deviceId || !this.deviceReady) {
                            this.pendingTrackUri = spotifyUri;
                            this.isLoading = true;
                            this.connectPlayer();
                            return;
                        }

                        try {
                            const tokenRes = await fetch('/spotify/token');
                            const tokenData = await tokenRes.json();
                            if (!tokenData.token) throw new Error('No token returned');

                            const headers = {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${tokenData.token}`
                            };

                            // 1. Wake up the specific device on Spotify's real API
                            await fetch('https://api.spotify.com/v1/me/player', {
                                method: 'PUT',
                                body: JSON.stringify({ device_ids: [this.deviceId], play: false }),
                                headers
                            }).catch(() => {});

                            // 2. Play the track on Spotify's real API
                            const res = await fetch(`https://api.spotify.com/v1/me/player/play?device_id=${this.deviceId}`, {
                                method: 'PUT',
                                body: JSON.stringify({ uris: [spotifyUri] }),
                                headers,
                            });

                            if (!res.ok) {
                                if (res.status === 404 && retryCount < 4) {
                                    const delay = 1500 * (retryCount + 1);
                                    this._retryTimer = setTimeout(() => this._doPlay(spotifyUri, meta, retryCount + 1), delay);
                                } else {
                                    // Retries exhausted on 404 — SDK connection is dead.
                                    // Explicitly disconnect the ghost player before nulling state,
                                    // so its stale player_state_changed listener stops writing to Alpine.
                                    if (res.status === 404) {
                                        if (this.player) {
                                            try { this.player.disconnect(); } catch (e) {}
                                        }
                                        this.deviceId = null;
                                        this.deviceReady = false;
                                        this.player = null;
                                        this.pendingTrackUri = spotifyUri;
                                        this.connectPlayer();
                                        return;
                                    }
                                    this.isLoading = false;
                                    if (res.status === 403) this._playNativePreview(meta);
                                }
                            } else {
                                this.isLoading = false;
                                this.isPaused = false;
                                this.startPolling();
                            }
                        } catch (err) {
                            this.isLoading = false;
                        }
                        return;
                    }

                    this._playNativePreview(meta);
                },

                _playNativePreview(meta) {
                    const previewUrl = meta?.previewUrl || null;
                    this.isLoading = false;

                    if (!previewUrl) {
                        this.noPreview = true;
                        this.isPaused = true;
                        this.positionMs = 0;
                        this.durationMs = 0;
                        return;
                    }

                    this.noPreview = false;
                    const audio = window.__spotifyNativeAudio;
                    audio.src = previewUrl;
                    audio.load();

                    audio.play().then(() => {
                        this.isPaused = false;
                        this.durationMs = 30000;
                    }).catch(err => {
                        this.isPaused = true;
                    });
                },

                togglePlay() {
                    if (this.isLoading || (this.noPreview && !this.isPremium)) return;

                    if (this.isPremium && this.player) {
                        this.player.togglePlay();
                    } else if (!this.isPremium) {
                        const audio = window.__spotifyNativeAudio;
                        if (!audio || !audio.src) return;
                        if (audio.paused) audio.play().then(() => { this.isPaused = false; }).catch(() => {});
                        else { audio.pause(); this.isPaused = true; }
                    }
                },

                seekRelative(deltaMs) {
                    if (this.isPremium && this.player) {
                        const newPos = Math.max(0, Math.min(this.durationMs, this.positionMs + deltaMs));
                        this.player.seek(newPos).then(() => { this.positionMs = newPos; });
                    } else if (!this.isPremium) {
                        const audio = window.__spotifyNativeAudio;
                        const newTime = Math.max(0, Math.min(audio.duration || 30, audio.currentTime + (deltaMs / 1000)));
                        audio.currentTime = newTime;
                        this.positionMs = newTime * 1000;
                    }
                },

                seekTo(event) {
                    const bar = event.currentTarget;
                    const rect = bar.getBoundingClientRect();
                    const ratio = (event.clientX - rect.left) / rect.width;

                    if (this.isPremium && this.player) {
                        const newPos = Math.max(0, Math.min(this.durationMs, ratio * this.durationMs));
                        this.player.seek(newPos).then(() => { this.positionMs = newPos; });
                    } else if (!this.isPremium) {
                        const audio = window.__spotifyNativeAudio;
                        const newTime = Math.max(0, Math.min(audio.duration || 30, ratio * (audio.duration || 30)));
                        audio.currentTime = newTime;
                        this.positionMs = newTime * 1000;
                    }
                },

                closePlayer() {
                    this.playerVisible = false;
                    if (this.isPremium && this.player) this.player.pause();
                    else window.__spotifyNativeAudio.pause();
                    
                    this.isPaused = true;
                    this.isLoading = false;
                    this.stopPolling();
                }
            }));
        }); // end alpine:init
    } // end __spotifyPlayerRegistered guard
    @endverbatim
    </script>
    @endpersist
@endif
