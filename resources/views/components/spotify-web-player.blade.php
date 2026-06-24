@if(auth()->check() && auth()->user()->spotify_token && auth()->user()->isSpotifyPremium())
<div x-data="spotifyWebPlayer()" 
     x-show="playerVisible"
     class="fixed bottom-0 left-0 right-0 md:left-auto md:right-4 md:bottom-4 md:w-96 z-50 pointer-events-none"
     style="display:none;"
     x-transition>
    <div class="bg-white dark:bg-black backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl p-4 shadow-2xl pointer-events-auto transition-colors duration-200">
        
        <!-- Header row: track info + collapse toggle + close -->
        <div class="flex items-center gap-4">
            <img :src="currentTrack?.album?.images[0]?.url" class="w-12 h-12 rounded-lg shadow-md shrink-0" alt="Album Art">
            <div class="flex-1 min-w-0">
                <p class="text-slate-900 dark:text-white font-bold text-sm truncate" x-text="currentTrack?.name"></p>
                <p class="text-slate-500 dark:text-zinc-400 text-xs truncate" x-text="currentTrack?.artists?.map(a => a.name).join(', ')"></p>
            </div>

            <!-- Collapse/expand toggle -->
            <button @click="collapsed = !collapsed" class="text-slate-400 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-white p-1 transition-all" :class="collapsed ? '' : 'rotate-180'" title="Collapse/Expand">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>

            <!-- Full close -->
            <button @click="playerVisible = false; player?.pause()" class="text-slate-400 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-white p-1 transition-colors" title="Close player">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
            </button>
        </div>

        <!-- Collapsible body: timeline + controls -->
        <div x-show="!collapsed" x-transition>
            <!-- White progress timeline -->
            <div class="mt-3">
                <div class="relative h-1.5 bg-slate-200 dark:bg-white/20 rounded-full cursor-pointer group"
                     @click="seekTo($event)">
                    <div class="absolute top-0 left-0 h-full bg-slate-800 dark:bg-white rounded-full transition-all"
                         :style="`width: ${progressPercent}%`"></div>
                    <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-slate-800 dark:bg-white rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                         :style="`left: calc(${progressPercent}% - 6px)`"></div>
                </div>
                <div class="flex justify-between mt-1 text-[11px] text-slate-500 dark:text-zinc-400">
                    <span x-text="formatTime(positionMs)"></span>
                    <span x-text="formatTime(durationMs)"></span>
                </div>
            </div>

            <!-- Controls: back 10s, play/pause, forward 10s -->
            <div class="flex items-center justify-center gap-6 mt-3">
                <button @click="seekRelative(-10000)" class="text-slate-700 hover:text-black dark:text-zinc-300 dark:hover:text-white hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                </button>
                <button @click="togglePlay" class="text-white bg-slate-900 hover:bg-black dark:text-black dark:bg-white dark:hover:bg-zinc-200 hover:scale-110 transition-transform rounded-full p-3 flex items-center justify-center">
                    <svg x-show="!isPaused" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25Zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25Z" clip-rule="evenodd"/></svg>
                    <svg x-show="isPaused" style="display:none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd"/></svg>
                </button>
                <button @click="seekRelative(10000)" class="text-slate-700 hover:text-black dark:text-zinc-300 dark:hover:text-white hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" style="transform: scaleX(-1)"><path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('spotifyWebPlayer', () => ({
            player: null,
            deviceId: null,
            isPlaying: false,
            isPaused: true,
            currentTrack: null,
            sdkInitialized: false,
            playerVisible: false,
            collapsed: false,
            positionMs: 0,
            durationMs: 0,
            progressInterval: null,

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

            isSdkReady() {
                return typeof Spotify !== 'undefined' && typeof Spotify.Player !== 'undefined';
            },

            init() {
                console.log('Alpine spotifyWebPlayer component init() called. Global player exists:', !!window.__spotifyPlayer);
                // Restore state from global cache if a running player already exists
                if (window.__spotifyPlayer) {
                    console.log('Restoring global player state.');
                    this.player = window.__spotifyPlayer;
                    this.deviceId = window.__spotifyPlayerDeviceId;
                    this.sdkInitialized = true;
                    window._spotifyReady = true;

                    // Restore UI states
                    this.playerVisible = !!window.__spotifyPlayerVisible;
                    this.collapsed = !!window.__spotifyPlayerCollapsed;

                    // Restore play status and metadata
                    if (window.__spotifyPlayerState) {
                        this.currentTrack = window.__spotifyPlayerState.currentTrack;
                        this.isPaused = window.__spotifyPlayerState.isPaused;
                        this.isPlaying = window.__spotifyPlayerState.isPlaying;
                        this.positionMs = window.__spotifyPlayerState.positionMs;
                        this.durationMs = window.__spotifyPlayerState.durationMs;
                    }

                    // Start polling position if playing
                    if (!this.isPaused) {
                        this.startPolling();
                    }
                } else {
                    window._spotifyReady = false;
                    window._pendingTrackUri = null;
                }

                // Register this instance to receive live updates from the global player
                window.__activeSpotifyPlayerComponent = this;

                // Watchers to update global UI state
                this.$watch('collapsed', value => {
                    window.__spotifyPlayerCollapsed = value;
                });
                this.$watch('playerVisible', value => {
                    window.__spotifyPlayerVisible = value;
                });

                // Global toggle — called by every song card's Spotify icon
                window.toggleSpotifyPlayer = (spotifyUri) => {
                    this.playerVisible = !this.playerVisible;
                    window.__spotifyPlayerVisible = this.playerVisible;
                    if (this.playerVisible) {
                        this.collapsed = false; // always show controls when reopened
                        window.__spotifyPlayerCollapsed = false;
                        window.playSpotifyTrack(spotifyUri);
                    } else {
                        if (this.player) this.player.pause();
                    }
                };

                window.playSpotifyTrack = async (spotifyUri) => {
                    this.playerVisible = true;
                    window.__spotifyPlayerVisible = true;
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
                    if (window._pendingTrackUri) this.connectPlayer();
                };

                // Register cleanup by returning it (Alpine 3 standard)
                return () => {
                    this.cleanupPlayer();
                };
            },

            cleanupPlayer() {
                console.log('cleanupPlayer called (no-op to prevent transition freezes).');
            },

            destroy() {
                console.log('destroy called (no-op to prevent transition freezes).');
            },

            startPolling() {
                this.stopPolling();
                this.progressInterval = setInterval(() => {
                    if (this.player && !this.isPaused) {
                        this.player.getCurrentState().then(state => {
                            if (state) {
                                this.positionMs = state.position;
                                this.durationMs = state.duration;
                                this.isPaused = state.paused;
                                this.currentTrack = state.track_window.current_track;

                                window.__spotifyPlayerState = {
                                    currentTrack: this.currentTrack,
                                    isPaused: this.isPaused,
                                    isPlaying: this.isPlaying,
                                    positionMs: this.positionMs,
                                    durationMs: this.durationMs
                                };
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
                console.log('connectPlayer called. sdkInitialized:', this.sdkInitialized, 'hasGlobalPlayer:', !!window.__spotifyPlayer);
                if (this.sdkInitialized && window.__spotifyPlayer) return;

                if (!this.isSdkReady()) {
                    console.warn('Spotify SDK not loaded yet. Queuing connectPlayer...');
                    const prevOnReady = window.onSpotifyWebPlaybackSDKReady;
                    window.onSpotifyWebPlaybackSDKReady = () => {
                        console.log('Deferred onSpotifyWebPlaybackSDKReady triggered.');
                        if (prevOnReady) prevOnReady();
                        this.connectPlayer();
                    };
                    return;
                }

                this.sdkInitialized = true;

                if (window.__spotifyPlayer) {
                    console.log('Restoring global Spotify player instance.');
                    this.player = window.__spotifyPlayer;
                    this.deviceId = window.__spotifyPlayerDeviceId;
                    if (!this.isPaused) {
                        this.startPolling();
                    }
                    return;
                }

                console.log('Creating new Spotify.Player instance...');
                const player = new Spotify.Player({
                    name: 'Reso Web Player',
                    getOAuthToken: cb => {
                        console.log('Fetching Spotify OAuth token...');
                        fetch('/spotify/token')
                            .then(response => response.json())
                            .then(data => {
                                if (data.token) {
                                    console.log('OAuth token fetched successfully.');
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
                window.__spotifyPlayer = player;

                player.addListener('initialization_error', ({ message }) => { console.error('init_error:', message); });
                player.addListener('authentication_error', ({ message }) => { console.error('auth_error:', message); });
                player.addListener('account_error', ({ message }) => { console.error('account_error:', message); });
                player.addListener('playback_error', ({ message }) => { console.error('playback_error:', message); });

                player.addListener('player_state_changed', state => {
                    console.log('player_state_changed fired. State:', state);
                    if (!state) return;
                    
                    const data = {
                        currentTrack: state.track_window.current_track,
                        isPaused: state.paused,
                        isPlaying: true,
                        positionMs: state.position,
                        durationMs: state.duration
                    };

                    window.__spotifyPlayerState = data;

                    if (window.__activeSpotifyPlayerComponent) {
                        console.log('Updating active player component with state data.');
                        window.__activeSpotifyPlayerComponent.currentTrack = data.currentTrack;
                        window.__activeSpotifyPlayerComponent.isPaused = data.isPaused;
                        window.__activeSpotifyPlayerComponent.isPlaying = data.isPlaying;
                        window.__activeSpotifyPlayerComponent.positionMs = data.positionMs;
                        window.__activeSpotifyPlayerComponent.durationMs = data.durationMs;

                        if (!data.isPaused) {
                            window.__activeSpotifyPlayerComponent.startPolling();
                        } else {
                            window.__activeSpotifyPlayerComponent.stopPolling();
                        }
                    }
                });

                player.addListener('ready', ({ device_id }) => {
                    console.log('Spotify Web Playback SDK is Ready with Device ID:', device_id);
                    this.deviceId = device_id;
                    window.__spotifyPlayerDeviceId = device_id;
                    window._spotifyReady = true;

                    // Flush any track that was clicked before we were ready
                    if (window._pendingTrackUri) {
                        const uri = window._pendingTrackUri;
                        window._pendingTrackUri = null;
                        console.log('Flushing pending track uri:', uri);
                        this._doPlay(uri);
                    }
                });

                player.addListener('not_ready', ({ device_id }) => {
                    console.log('Device ID has gone offline', device_id);
                    window._spotifyReady = false;
                });

                console.log('Connecting to Spotify (with DOM Interceptor)...');
                
                const originalBodyAppend = document.body.appendChild;
                const originalBodyInsertBefore = document.body.insertBefore;
                const container = this.$el;

                const interceptor = function(element) {
                    if (element && element.tagName === 'IFRAME' && element.src && (element.src.includes('sdk.scdn.co') || element.src.includes('spotify'))) {
                        console.log('Intercepted Spotify SDK iframe. Placing inside persisted container...');
                        element.style.display = 'none';
                        element.style.width = '0px';
                        element.style.height = '0px';
                        element.style.position = 'absolute';
                        container.appendChild(element);
                        return element;
                    }
                    return null;
                };

                document.body.appendChild = function(element) {
                    const intercepted = interceptor(element);
                    if (intercepted) return intercepted;
                    return originalBodyAppend.apply(this, arguments);
                };

                document.body.insertBefore = function(element, reference) {
                    const intercepted = interceptor(element);
                    if (intercepted) return intercepted;
                    return originalBodyInsertBefore.apply(this, arguments);
                };

                player.connect();

                // Restore original methods
                setTimeout(() => {
                    document.body.appendChild = originalBodyAppend;
                    document.body.insertBefore = originalBodyInsertBefore;
                    console.log('Restored original document.body DOM methods.');
                }, 2000);

                if (!this.isPaused) {
                    this.startPolling();
                }
            },

            async _doPlay(spotifyUri, retryCount = 0) {
                console.log(`_doPlay called for URI: ${spotifyUri}, deviceId: ${this.deviceId}, retryCount: ${retryCount}`);
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

                        // If 404 (Device not found) and we haven't retried too many times, retry after a delay
                        if (res.status === 404 && retryCount < 3) {
                            console.log(`Device not found (404). Retrying play request in 1.5s (attempt ${retryCount + 1}/3)...`);
                            setTimeout(() => {
                                this._doPlay(spotifyUri, retryCount + 1);
                            }, 1500);
                        }
                    } else {
                        console.log('Spotify play request succeeded.');
                    }
                } catch (err) {
                    console.error('Failed to play track:', err);
                }
            },

            togglePlay() {
                if (this.player) {
                    this.player.togglePlay();
                }
            },

            seekRelative(deltaMs) {
                const newPos = Math.max(0, Math.min(this.durationMs, this.positionMs + deltaMs));
                this.player?.seek(newPos).then(() => {
                    this.positionMs = newPos;
                    if (window.__spotifyPlayerState) {
                        window.__spotifyPlayerState.positionMs = newPos;
                    }
                });
            },

            seekTo(event) {
                const bar = event.currentTarget;
                const rect = bar.getBoundingClientRect();
                const ratio = (event.clientX - rect.left) / rect.width;
                const newPos = Math.max(0, Math.min(this.durationMs, ratio * this.durationMs));
                this.player?.seek(newPos).then(() => {
                    this.positionMs = newPos;
                    if (window.__spotifyPlayerState) {
                        window.__spotifyPlayerState.positionMs = newPos;
                    }
                });
            }
        }));
    });
</script>
@endif
