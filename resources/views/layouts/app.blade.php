<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script>
            // Dark Mode
            const theme = localStorage.getItem('theme') 
              ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', theme === 'dark');

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
              if (!localStorage.getItem('theme')) {
                document.documentElement.classList.toggle('dark', e.matches);
              }
            });

            document.addEventListener('livewire:navigated', () => {
                const theme = localStorage.getItem('theme') 
                  ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', theme === 'dark');
            });

            // Scroll Position Persistence
            window.reloadWithScroll = function() {
                sessionStorage.setItem('scrollPosition', window.scrollY);
                window.location.reload();
            };

            document.addEventListener("DOMContentLoaded", function(event) { 
                var scrollpos = sessionStorage.getItem('scrollPosition');
                if (scrollpos) {
                    window.scrollTo(0, scrollpos);
                    sessionStorage.removeItem('scrollPosition');
                }
            });

            /**
             * Global Spotify iframe preview toggle.
             * Used by sidebar-right.blade.php and playlists/show.blade.php.
             *
             * @param {string} key         - Unique key e.g. 'sid-42' or 'ply-99'
             * @param {string} trackId     - Spotify track ID (not full URL)
             */
            window._activeSpotifyKey = null;

            window.toggleSpotifyPreview = function(key, trackId) {
                if (!trackId) return;

                const container = document.getElementById('spe-container-' + key);
                const frame     = document.getElementById('spe-frame-' + key);
                const playIcon  = document.getElementById('spe-icon-play-' + key);
                const stopIcon  = document.getElementById('spe-icon-stop-' + key);

                if (!container || !frame) return;

                const embedUrl = 'https://open.spotify.com/embed/track/' + trackId + '?utm_source=generator&theme=0';
                const isOpen   = container.style.display !== 'none';

                // Close any other open preview first
                if (window._activeSpotifyKey && window._activeSpotifyKey !== key) {
                    const prevContainer = document.getElementById('spe-container-' + window._activeSpotifyKey);
                    const prevFrame     = document.getElementById('spe-frame-'     + window._activeSpotifyKey);
                    const prevPlay      = document.getElementById('spe-icon-play-' + window._activeSpotifyKey);
                    const prevStop      = document.getElementById('spe-icon-stop-' + window._activeSpotifyKey);
                    if (prevContainer) prevContainer.style.display = 'none';
                    if (prevFrame)     prevFrame.src = '';
                    if (prevPlay)      prevPlay.style.display  = '';
                    if (prevStop)      prevStop.style.display  = 'none';
                }

                if (isOpen) {
                    // Close this one
                    container.style.display = 'none';
                    frame.src = '';
                    if (playIcon) playIcon.style.display = '';
                    if (stopIcon) stopIcon.style.display = 'none';
                    window._activeSpotifyKey = null;
                } else {
                    // Open this one
                    frame.src = embedUrl;
                    container.style.display = 'block';
                    if (playIcon) playIcon.style.display = 'none';
                    if (stopIcon) stopIcon.style.display = '';
                    window._activeSpotifyKey = key;
                }
            };
        </script>
        <style>
            /* Scale the entire UI down dynamically on small mobile devices to prevent element overlapping */
            @media (max-width: 480px) {
                html {
                    font-size: clamp(10px, 3.75vw, 16px);
                }
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(5px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fadeIn 0.3s ease-out forwards;
            }
        </style>
        {{-- ============================================================
             Spotify Web Playback SDK — initialised ONCE in <head>.
             wire:navigate never re-executes <head> scripts, so this
             block runs only on a hard page load. The player instance,
             device ID and ready state are stored on window so the
             Alpine UI component can read them without managing SDK
             lifecycle at all.
        ============================================================ --}}
        @auth
        @if(auth()->user()->spotify_token && auth()->user()->isSpotifyPremium())
        <script>
            window.SpotifySDKLoaded = false;
            window.onSpotifyWebPlaybackSDKReady = () => {
                window.SpotifySDKLoaded = true;
                window.dispatchEvent(new Event('spotify-sdk-loaded'));
            };
        </script>
        <script src="https://sdk.scdn.co/spotify-player.js"></script>
        @endif
        @endauth
    </head>
    <body class="font-sans antialiased bg-white dark:bg-black dark:text-gray-100 min-h-screen transition-colors duration-300">
        <div class="min-h-screen">

            <!-- [FIXED] This logic MUST come BEFORE the navigation is included -->
            @php
                // Get the 'pageTitle' attribute passed from the view
                // e.g., <x-app-layout pageTitle="Home Feed">
                $pageTitle = $attributes->get('pageTitle');

                // Share it with all views (specifically for navigation.blade.php)
                if ($pageTitle) {
                    View::share('pageTitle', $pageTitle);
                }
            @endphp

            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="animate-fade-in pb-20 md:pb-0">
                {{ $slot }}
            </main>
        </div>
        
        <x-mobile-bottom-nav />
        <x-add-to-playlist-modal />
        <x-spotify-link-modal />
        <x-add-to-reso-playlist-modal />
        <x-spotify-web-player />

        {{-- =====================================================
             Global Playback Chooser Modal
             Triggered by: $dispatch('open-playback-chooser', { spotifyUrl, youtubeUrl, trackName, artistName })
             Used by: sidebar-right, discovery pages
        ===================================================== --}}
        <div x-data="{
                show: false,
                spotifyUrl: '',
                youtubeUrl: '',
                trackName: '',
                artistName: ''
             }"
             x-on:open-playback-chooser.window="
                spotifyUrl  = $event.detail.spotifyUrl  || '';
                youtubeUrl  = $event.detail.youtubeUrl  || '';
                trackName   = $event.detail.trackName   || '';
                artistName  = $event.detail.artistName  || '';
                show = true;
             "
             x-on:keydown.escape.window="show = false"
             x-show="show"
             style="display:none;"
             class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-4">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="show = false"></div>

            {{-- Panel --}}
            <div class="relative w-full max-w-sm bg-white dark:bg-[#141414] rounded-3xl shadow-2xl border border-gray-100 dark:border-white/10 overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95">

                {{-- Header --}}
                <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-white/5">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Play track</p>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" x-text="trackName"></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate" x-text="artistName"></p>
                </div>

                {{-- Options --}}
                <div class="p-4 space-y-3">
                    {{-- Spotify --}}
                    <a :href="spotifyUrl" target="_blank" rel="noopener noreferrer"
                       @click="show = false"
                       class="flex items-center gap-4 w-full px-5 py-4 rounded-2xl bg-[#1DB954]/10 hover:bg-[#1DB954]/20 dark:bg-[#1DB954]/10 dark:hover:bg-[#1DB954]/20 border border-[#1DB954]/20 dark:border-[#1DB954]/20 transition-all group">
                        <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1DB954] shadow-lg shadow-[#1DB954]/30 group-hover:scale-110 transition-transform shrink-0">
                            <svg class="w-5 h-5" fill="white" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                        </span>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Open in Spotify</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Full track · Spotify Web or App</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    {{-- YouTube --}}
                    <a :href="youtubeUrl" target="_blank" rel="noopener noreferrer"
                       @click="show = false"
                       class="flex items-center gap-4 w-full px-5 py-4 rounded-2xl bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 border border-red-100 dark:border-red-500/20 transition-all group">
                        <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#FF0000] shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform shrink-0">
                            <svg class="w-5 h-5" fill="white" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </span>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Open in YouTube</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Music video · YouTube Web or App</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                {{-- Cancel --}}
                <div class="px-4 pb-5">
                    <button @click="show = false"
                            class="w-full py-3 rounded-2xl text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
