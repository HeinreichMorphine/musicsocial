@if(auth()->check() && auth()->user()->spotify_token && auth()->user()->isSpotifyPremium())
<div x-data="spotifyWebPlayer()" 
     x-show="playerVisible"
     class="fixed bottom-0 left-0 right-0 md:left-auto md:right-4 md:bottom-4 md:w-96 z-50 pointer-events-none"
     style="display:none;"
     x-transition>
    <div class="bg-white dark:bg-black backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl p-4 shadow-2xl pointer-events-auto transition-colors duration-200">
        
        <!-- Header row: track info + collapse toggle + close -->
        <div class="flex items-center gap-4">
            <img :src="albumArt || '/images/default-album-art.png'" class="w-12 h-12 rounded-lg shadow-md shrink-0" alt="Album Art">
            <div class="flex-1 min-w-0">
                <p class="text-slate-900 dark:text-white font-bold text-sm truncate" x-text="trackName || 'Select a track'"></p>
                <p class="text-slate-500 dark:text-zinc-400 text-xs truncate" x-text="artistName || ''"></p>
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
            deviceReady: false,       // True only after SDK 'ready' + registration delay
            isPlaying: false,
            isPaused: true,
            playerVisible: false,
            collapsed: false,
            positionMs: 0,
            durationMs: 0,
            progressInterval: null,

            // Preloaded metadata from database (shown instantly in UI)
            trackName: null,
            artistName: null,
            albumArt: null,

            // Pending play request
            pendingTrackUri: null,
            pendingMeta: null,

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
                console.log('Spotify Player initializing...');

                // Global function called by share-card, discovery-card, comment etc.
                // Signature: toggleSpotifyPlayer(spotifyUri, meta?)
                //   meta = { name, artist, art }  (optional, for instant UI)
                window.toggleSpotifyPlayer = (spotifyUri, meta) => {
                    this.playerVisible = true;
                    this.collapsed = false;

                    // Preload UI instantly from database metadata
                    if (meta) {
                        this.trackName = meta.name || null;
                        this.artistName = meta.artist || null;
                        this.albumArt = meta.art || null;
                    }

                    this._doPlay(spotifyUri);
                };

                window.onSpotifyWebPlaybackSDKReady = () => {
                    this.connectPlayer();
                };

                // Guard: If SDK is already loaded, connect immediately
                if (typeof Spotify !== 'undefined' && typeof Spotify.Player !== 'undefined') {
                    this.connectPlayer();
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
                                this.isPaused = state.paused;
                                // Update metadata from Spotify once playback starts
                                const ct = state.track_window.current_track;
                                if (ct) {
                                    this.trackName = ct.name;
                                    this.artistName = ct.artists?.map(a => a.name).join(', ');
                                    this.albumArt = ct.album?.images?.[0]?.url || this.albumArt;
                                }
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
                if (this.player) return;

                console.log('Creating new Spotify.Player instance...');
                const player = new Spotify.Player({
                    name: 'Reso Web Player',
                    getOAuthToken: cb => {
                        fetch('/spotify/token')
                            .then(res => res.json())
                            .then(data => { if (data.token) cb(data.token); })
                            .catch(err => console.error('Token fetch error:', err));
                    },
                    volume: 0.5
                });

                player.addListener('initialization_error', ({ message }) => { console.error('init_error:', message); });
                player.addListener('authentication_error', ({ message }) => { console.error('auth_error:', message); });
                player.addListener('account_error', ({ message }) => { console.error('account_error:', message); });
                player.addListener('playback_error', ({ message }) => { console.error('playback_error:', message); });

                player.addListener('player_state_changed', state => {
                    if (!state) return;
                    this.isPaused = state.paused;
                    this.positionMs = state.position;
                    this.durationMs = state.duration;

                    // Update metadata from actual Spotify state
                    const ct = state.track_window.current_track;
                    if (ct) {
                        this.trackName = ct.name;
                        this.artistName = ct.artists?.map(a => a.name).join(', ');
                        this.albumArt = ct.album?.images?.[0]?.url || this.albumArt;
                    }

                    if (!this.isPaused) this.startPolling();
                    else this.stopPolling();
                });

                player.addListener('ready', ({ device_id }) => {
                    console.log('Spotify SDK Ready (raw):', device_id);
                    this.deviceId = device_id;
                    this.player = player;

                    // CRITICAL: The device_id is NOT immediately usable on Spotify's servers.
                    // The SDK fires 'ready' before the device fully registers upstream.
                    // We must wait ~2s before sending any API calls targeting this device.
                    console.log('Waiting 2s for device to register with Spotify servers...');
                    setTimeout(() => {
                        console.log('Device now considered fully registered:', device_id);
                        this.deviceReady = true;

                        // Flush any pending track
                        if (this.pendingTrackUri) {
                            const uri = this.pendingTrackUri;
                            const meta = this.pendingMeta;
                            this.pendingTrackUri = null;
                            this.pendingMeta = null;
                            this._doPlay(uri);
                        }
                    }, 2000);
                });

                player.addListener('not_ready', ({ device_id }) => {
                    console.log('Device ID has gone offline', device_id);
                    this.deviceReady = false;
                });

                player.connect();
            },

            async _doPlay(spotifyUri, retryCount = 0) {
                console.log(`_doPlay: uri=${spotifyUri}, deviceId=${this.deviceId}, ready=${this.deviceReady}, retry=${retryCount}`);

                // If player/device not ready yet, queue the track
                if (!this.deviceId || !this.deviceReady) {
                    console.log('Player not ready yet. Queuing track:', spotifyUri);
                    this.pendingTrackUri = spotifyUri;
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

                    // Step 1: Transfer playback to wake up the device
                    console.log('Transferring playback to device:', this.deviceId);
                    const transferRes = await fetch('https://api.spotify.com/v1/me/player', {
                        method: 'PUT',
                        body: JSON.stringify({ device_ids: [this.deviceId], play: false }),
                        headers
                    });

                    // If transfer returns 404, the device still isn't registered — retry with backoff
                    if (!transferRes.ok && transferRes.status === 404) {
                        if (retryCount < 4) {
                            const delay = 1500 * (retryCount + 1); // 1.5s, 3s, 4.5s, 6s
                            console.log(`Transfer 404 — device not registered yet, retrying in ${delay}ms (${retryCount + 1}/4)`);
                            setTimeout(() => this._doPlay(spotifyUri, retryCount + 1), delay);
                            return;
                        }
                        console.error('Transfer playback failed after all retries. Device may not be available.');
                        return;
                    }

                    // Step 2: Small delay to let transfer settle
                    await new Promise(r => setTimeout(r, 300));

                    // Step 3: Play the track
                    const res = await fetch(`https://api.spotify.com/v1/me/player/play?device_id=${this.deviceId}`, {
                        method: 'PUT',
                        body: JSON.stringify({ uris: [spotifyUri] }),
                        headers,
                    });

                    if (!res.ok) {
                        const errBody = await res.text();
                        console.error('Spotify play request failed:', res.status, errBody);

                        if (res.status === 404 && retryCount < 4) {
                            const delay = 1500 * (retryCount + 1);
                            console.log(`Play 404 — retrying in ${delay}ms (${retryCount + 1}/4)`);
                            setTimeout(() => this._doPlay(spotifyUri, retryCount + 1), delay);
                        }
                    } else {
                        console.log('Spotify play request succeeded.');
                        this.isPaused = false;
                        this.startPolling();
                    }
                } catch (err) {
                    console.error('Failed to play track:', err);
                }
            },

            togglePlay() {
                if (this.player) this.player.togglePlay();
            },

            seekRelative(deltaMs) {
                const newPos = Math.max(0, Math.min(this.durationMs, this.positionMs + deltaMs));
                this.player?.seek(newPos).then(() => {
                    this.positionMs = newPos;
                });
            },

            seekTo(event) {
                const bar = event.currentTarget;
                const rect = bar.getBoundingClientRect();
                const ratio = (event.clientX - rect.left) / rect.width;
                const newPos = Math.max(0, Math.min(this.durationMs, ratio * this.durationMs));
                this.player?.seek(newPos).then(() => {
                    this.positionMs = newPos;
                });
            }
        }));
    });
</script>
@endif
