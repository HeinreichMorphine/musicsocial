<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js for guest pages (Livewire not present here to provide it) -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <script>
            const theme = localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', theme === 'dark');
            
            function toggleTheme() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            }
        </script>

        <style>
            /* Scale the entire UI down dynamically on small mobile devices */
            @media (max-width: 480px) {
                html {
                    font-size: clamp(10px, 3.75vw, 16px);
                }
            }
        </style>
    </head>
    <body class="font-sans text-slate-900 dark:text-white antialiased bg-slate-50 dark:bg-black selection:bg-custom-periwinkle dark:selection:bg-brand selection:text-custom-dark-blue dark:selection:text-white transition-colors duration-200">
        <div class="min-h-screen w-full px-4 sm:px-6 py-12 flex flex-col items-center justify-center relative overflow-x-hidden">
            <!-- Theme Toggle -->
            <div class="absolute top-6 right-6 z-50">
                <button onclick="toggleTheme()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition p-2 rounded-full hover:bg-gray-200 dark:hover:bg-white/10">
                    <svg class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
            </div>

            {{-- Subtle background decoration elements --}}
            <div class="absolute top-0 inset-x-0 h-[220px] bg-gradient-to-b from-custom-periwinkle/10 dark:from-[#1DB954]/10 to-transparent pointer-events-none z-0"></div>

            <div class="flex flex-col items-center w-full max-w-md relative z-10">
                {{-- Logo --}}
                <div class="mb-5 transform hover:scale-[1.03] transition-all duration-300">
                    <a href="/" class="flex items-center justify-center">
                        <img src="{{ asset('icons/reso.png') }}" alt="Reso Logo" class="w-24 h-24 object-contain drop-shadow-sm">
                    </a>
                </div>

                {{-- Auth Card --}}
                <div class="w-full px-6 pt-6 pb-8 sm:px-10 sm:pt-8 sm:pb-10 bg-white dark:bg-[#121212] shadow-[0_15px_45px_rgba(22,42,114,0.06)] dark:shadow-[0_15px_45px_rgba(29,185,84,0.06)] border-t-4 border-t-custom-dark-blue dark:border-t-brand border-x border-b border-slate-200/80 dark:border-white/5 rounded-3xl transition-colors duration-200">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
