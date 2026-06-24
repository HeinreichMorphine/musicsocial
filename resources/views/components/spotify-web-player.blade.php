@if(auth()->check())
    @php 
        $hasToken = !empty(auth()->user()->spotify_token);
        $isPremiumUser = $hasToken && auth()->user()->isSpotifyPremium(); 
    @endphp

    {{--
        Pure UI component. The Spotify SDK is managed entirely in app.blade.php's <head>
        (window.SpotifyPlayerInstance / SpotifyDeviceId / SpotifyDeviceReady).
        This component just reads from those globals and dispatches playback commands.
        No @persist needed — Alpine state is re-hydrated from window on every init().
    --}}
    <div x-data="spotifyWebPlayer({ isPremium: {{ $isPremiumUser ? 'true' : 'false' }} })" 
         x-show="playerVisible"
         class="fixed bottom-0 left-0 right-0 md:left-auto md:right-4 md:bottom-4 md:w-96 z-50 pointer-events-none"
         style="display:none;"
         x-transition>

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
    if (!window.__spotifyNativeAudio) {
        window.__spotifyNativeAudio = new Audio();
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('spotifyWebPlayer', (config) => ({
            isPremium: config.isPremium,
            playerVisible: false,
            collapsed: false,
            isLoading: false,
            isPaused: true,
            positionMs: 0,
            durationMs: 0,
            progressInterval: null,
            noPreview: false,
            trackName: null,
            artistName: null,
            albumArt: null,
            pendingTrackUri: null,
            pendingMeta: null,

            // Self-contained player properties
            player: null,
            deviceId: null,
            deviceReady: false,

            get progressPercent() {
                return this.durationMs > 0 ? (this.positionMs / this.durationMs) * 100 : 0;
            },

            formatTime(ms) {
                if (!ms) return '0:00';
                const s = Math.floor(ms / 1000);
                return `${Math.floor(s / 60)}:${(s % 60).toString().padStart(2, '0')}`;
            },

            init() {
                console.log('[SpotifyPlayer] Alpine mounted — isPremium:', this.isPremium);

                if (!window.__spotifyAudioListenersAttached) {
                    window.__spotifyAudioListenersAttached = true;
                    window.__spotifyNativeAudio.addEventListener('timeupdate', () => {
                        if (!this.isPremium) this.positionMs = window.__spotifyNativeAudio.currentTime * 1000;
                    });
                    window.__spotifyNativeAudio.addEventListener('ended', () => {
                        if (!this.isPremium) { this.isPaused = true; this.positionMs = 0; }
                    });
                }

                if (this.isPremium) {
                    this.initializePlayer();
                }

                // Register global play trigger
                window.toggleSpotifyPlayer = (spotifyUri, meta) => {
                    this.playerVisible = true;
                    this.collapsed     = false;
                    this.noPreview     = false;
                    this.isLoading     = true;

                    if (meta) {
                        this.trackName  = meta.name   || null;
                        this.artistName = meta.artist || null;
                        this.albumArt   = meta.art    || null;
                    }

                    this._doPlay(spotifyUri, meta);
                };
            },

            initializePlayer() {
                if (!this.isPremium) return;

                console.log('[SpotifyPlayer] Running initializePlayer (nuclear reset & script injection)');

                // 1. Remove previous script tag if present
                const existingScript = document.getElementById('spotify-sdk-script');
                if (existingScript) {
                    console.log('[SpotifyPlayer] Removing existing script tag');
                    existingScript.remove();
                }

                // 2. Remove lingering Spotify playback SDK iframe if present
                const existingIframe = document.getElementById('spotify-playback-sdk-iframe');
                if (existingIframe) {
                    console.log('[SpotifyPlayer] Removing lingering playback iframe');
                    existingIframe.remove();
                }

                // 3. Clear window namespaces and state flags
                if (window.Spotify) {
                    delete window.Spotify;
                }
                window.SpotifySDKLoaded = false;
                window.isSpotifyReady = false;

                // 4. Register the SDK ready callback globally
                window.onSpotifyWebPlaybackSDKReady = () => {
                    console.log('[SpotifyPlayer] onSpotifyWebPlaybackSDKReady callback fired');
                    window.SpotifySDKLoaded = true;
                    this.connectPlayer();
                };

                // 5. Inject a fresh Spotify SDK script tag dynamically into the head
                const script = document.createElement('script');
                script.id = 'spotify-sdk-script';
                script.src = 'https://sdk.scdn.co/spotify-player.js';
                script.async = true;
                document.head.appendChild(script);
            },

            connectPlayer() {
                if (this.player) return;
                if (!window.Spotify) {
                    console.warn('[SpotifyPlayer] connectPlayer called but window.Spotify is undefined. Waiting for script to load.');
                    return;
                }

                console.log('[SpotifyPlayer] Initializing new Spotify Player instance inside component');
                const player = new Spotify.Player({
                    name: 'Reso Web Player',
                    getOAuthToken: cb => {
                        fetch('/spotify/token')
                            .then(r => r.json())
                            .then(d => { if (d.token) cb(d.token); })
                            .catch(err => console.error('[SpotifyPlayer] token fetch failed:', err));
                    },
                    volume: 0.5
                });

                player.addListener('ready', ({ device_id }) => {
                    console.log('[SpotifyPlayer] SDK ready, device_id:', device_id);
                    this.deviceId = device_id;
                    this.deviceReady = true;
                    this.isLoading = false;
                    
                    window.isSpotifyReady = true;
                    window.dispatchEvent(new CustomEvent('spotify-ready', { detail: { device_id } }));

                    if (this.pendingTrackUri) {
                        const uri = this.pendingTrackUri;
                        const meta = this.pendingMeta;
                        this.pendingTrackUri = null;
                        this.pendingMeta = null;
                        this._doPlay(uri, meta);
                    }
                });

                player.addListener('not_ready', ({ device_id }) => {
                    console.warn('[SpotifyPlayer] SDK device offline:', device_id);
                    this.deviceReady = false;
                    this.isLoading = false;
                    
                    window.isSpotifyReady = false;
                    window.dispatchEvent(new Event('spotify-not-ready'));
                });

                player.addListener('player_state_changed', state => {
                    if (!state) return;
                    this.isPaused = state.paused;
                    this.positionMs = state.position;
                    this.durationMs = state.duration;

                    // Update metadata if available from the state
                    const currentTrack = state.track_window?.current_track;
                    if (currentTrack) {
                        this.trackName = currentTrack.name;
                        this.artistName = currentTrack.artists.map(a => a.name).join(', ');
                        this.albumArt = currentTrack.album.images[0]?.url || null;
                    }

                    if (!this.isPaused) this.startPolling();
                    else this.stopPolling();
                });

                player.addListener('initialization_error', ({ message }) => {
                    console.error('[SpotifyPlayer] initialization_error:', message);
                    this.isLoading = false;
                    window.isSpotifyReady = false;
                    window.dispatchEvent(new Event('spotify-not-ready'));
                });
                player.addListener('authentication_error', ({ message }) => {
                    console.error('[SpotifyPlayer] authentication_error:', message);
                    this.isLoading = false;
                    window.isSpotifyReady = false;
                    window.dispatchEvent(new Event('spotify-not-ready'));
                });
                player.addListener('account_error', ({ message }) => {
                    console.error('[SpotifyPlayer] account_error:', message);
                    this.isLoading = false;
                    window.isSpotifyReady = false;
                    window.dispatchEvent(new Event('spotify-not-ready'));
                });
                player.addListener('playback_error', ({ message }) => {
                    console.error('[SpotifyPlayer] playback_error:', message);
                    this.isLoading = false;
                });

                this.player = player;
                player.connect();
            },

            destroy() {
                console.log('[SpotifyPlayer] Component unmounting. Disconnecting player.');
                if (this.player) {
                    try {
                        this.player.disconnect();
                    } catch (e) {}
                    this.player = null;
                }
                this.deviceId = null;
                this.deviceReady = false;
                window.isSpotifyReady = false;
                window.dispatchEvent(new Event('spotify-not-ready'));
                this.stopPolling();

                if (window.__spotifyNativeAudio) {
                    try {
                        window.__spotifyNativeAudio.pause();
                    } catch (e) {}
                }
            },

            startPolling() {
                this.stopPolling();
                this.progressInterval = setInterval(() => {
                    if (this.player && !this.isPaused) {
                        this.player.getCurrentState().then(state => {
                            if (state) {
                                this.positionMs = state.position;
                                this.durationMs = state.duration;
                                this.isPaused   = state.paused;
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

            async _doPlay(spotifyUri, meta) {
                if (this.isPremium) {
                    if (!this.deviceReady || !this.deviceId) {
                        console.log('[SpotifyPlayer] Device not ready yet — queuing track');
                        this.pendingTrackUri = spotifyUri;
                        this.pendingMeta     = meta;
                        this.isLoading       = true;
                        this.connectPlayer();
                        return;
                    }

                    try {
                        const tokenRes  = await fetch('/spotify/token');
                        const tokenData = await tokenRes.json();
                        if (!tokenData.token) throw new Error('No token');

                        const headers = {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${tokenData.token}`
                        };

                        await fetch('https://api.spotify.com/v1/me/player', {
                            method: 'PUT',
                            body: JSON.stringify({ device_ids: [this.deviceId], play: false }),
                            headers
                        }).catch(() => {});

                        const res = await fetch(`https://api.spotify.com/v1/me/player/play?device_id=${this.deviceId}`, {
                            method: 'PUT',
                            body: JSON.stringify({ uris: [spotifyUri] }),
                            headers
                        });

                        if (!res.ok) {
                            console.warn('[SpotifyPlayer] play failed:', res.status);
                            if (res.status === 403) this._playNativePreview(meta);
                            else this.isLoading = false;
                        } else {
                            this.isLoading = false;
                            this.isPaused  = false;
                            this.startPolling();
                        }
                    } catch (err) {
                        console.error('[SpotifyPlayer] _doPlay error:', err);
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
                    this.noPreview  = true;
                    this.isPaused   = true;
                    this.positionMs = 0;
                    this.durationMs = 0;
                    return;
                }

                this.noPreview = false;
                const audio = window.__spotifyNativeAudio;
                audio.src = previewUrl;
                audio.load();
                audio.play()
                    .then(() => { this.isPaused = false; this.durationMs = 30000; })
                    .catch(() => { this.isPaused = true; });
            },

            togglePlay() {
                if (this.isLoading || (this.noPreview && !this.isPremium)) return;

                if (this.isPremium && this.player) {
                    this.player.togglePlay();
                } else if (!this.isPremium) {
                    const audio = window.__spotifyNativeAudio;
                    if (!audio?.src) return;
                    if (audio.paused) audio.play().then(() => { this.isPaused = false; }).catch(() => {});
                    else { audio.pause(); this.isPaused = true; }
                }
            },

            seekRelative(deltaMs) {
                if (this.isPremium && this.player) {
                    const newPos = Math.max(0, Math.min(this.durationMs, this.positionMs + deltaMs));
                    this.player.seek(newPos).then(() => { this.positionMs = newPos; });
                } else if (!this.isPremium) {
                    const audio   = window.__spotifyNativeAudio;
                    const newTime = Math.max(0, Math.min(audio.duration || 30, audio.currentTime + (deltaMs / 1000)));
                    audio.currentTime = newTime;
                    this.positionMs   = newTime * 1000;
                }
            },

            seekTo(event) {
                const rect  = event.currentTarget.getBoundingClientRect();
                const ratio = (event.clientX - rect.left) / rect.width;

                if (this.isPremium && this.player) {
                    const newPos = Math.max(0, Math.min(this.durationMs, ratio * this.durationMs));
                    this.player.seek(newPos).then(() => { this.positionMs = newPos; });
                } else if (!this.isPremium) {
                    const audio   = window.__spotifyNativeAudio;
                    const newTime = Math.max(0, Math.min(audio.duration || 30, ratio * (audio.duration || 30)));
                    audio.currentTime = newTime;
                    this.positionMs   = newTime * 1000;
                }
            },

            closePlayer() {
                this.playerVisible = false;
                if (this.isPremium && this.player) this.player.pause();
                else window.__spotifyNativeAudio?.pause();
                this.isPaused  = true;
                this.isLoading = false;
                this.stopPolling();
            }
        }));
    });
    @endverbatim
    </script>
@endif