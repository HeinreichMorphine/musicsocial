<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
