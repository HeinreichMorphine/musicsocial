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
        
        <style>
            /* Scale the entire UI down dynamically on small mobile devices */
            @media (max-width: 480px) {
                html {
                    font-size: clamp(10px, 3.75vw, 16px);
                }
            }
        </style>
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50/50 selection:bg-custom-periwinkle selection:text-custom-dark-blue">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">
            {{-- Subtle background decoration elements --}}
            <div class="absolute top-0 inset-x-0 h-[220px] bg-gradient-to-b from-custom-periwinkle/10 to-transparent pointer-events-none z-0"></div>

            <div class="mb-2 transform hover:scale-[1.03] transition-all duration-300 relative z-10">
                <a href="/" class="flex items-center justify-center w-16 h-16 rounded-full bg-custom-periwinkle/20 border border-custom-periwinkle/30 shadow-sm hover:bg-custom-periwinkle/30 transition-colors">
                    <img src="{{ asset('icons/reso.png') }}" alt="Reso Logo" class="w-9 h-9 object-contain drop-shadow-sm">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-10 py-10 bg-white shadow-[0_15px_50px_rgba(22,42,114,0.03)] border-t-4 border-t-custom-dark-blue border-x border-b border-slate-100 sm:rounded-3xl relative z-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
