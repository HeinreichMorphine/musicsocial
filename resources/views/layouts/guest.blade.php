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
        
        <script>document.documentElement.classList.remove('dark');</script>

        <style>
            /* Scale the entire UI down dynamically on small mobile devices */
            @media (max-width: 480px) {
                html {
                    font-size: clamp(10px, 3.75vw, 16px);
                }
            }
        </style>
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50 selection:bg-custom-periwinkle selection:text-custom-dark-blue">
        <div class="min-h-screen w-full px-4 sm:px-6 py-12 flex flex-col items-center justify-center relative overflow-x-hidden">
            {{-- Subtle background decoration elements --}}
            <div class="absolute top-0 inset-x-0 h-[220px] bg-gradient-to-b from-custom-periwinkle/10 to-transparent pointer-events-none z-0"></div>

            <div class="flex flex-col items-center w-full max-w-md relative z-10">
                {{-- Logo --}}
                <div class="mb-5 transform hover:scale-[1.03] transition-all duration-300">
                    <a href="/" class="flex items-center justify-center">
                        <img src="{{ asset('icons/reso.png') }}" alt="Reso Logo" class="w-24 h-24 object-contain drop-shadow-sm">
                    </a>
                </div>

                {{-- Auth Card --}}
                <div class="w-full px-6 pt-6 pb-8 sm:px-10 sm:pt-8 sm:pb-10 bg-white shadow-[0_15px_45px_rgba(22,42,114,0.06)] border-t-4 border-t-custom-dark-blue border-x border-b border-slate-200/80 rounded-3xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
