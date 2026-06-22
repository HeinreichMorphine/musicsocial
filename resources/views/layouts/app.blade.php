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
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }

            document.addEventListener('livewire:navigated', () => {
                if (localStorage.getItem('darkMode') === 'true') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
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
        @livewireScripts
    </body>
</html>
