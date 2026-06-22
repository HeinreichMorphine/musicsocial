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
    <body class="font-sans text-gray-900 antialiased bg-gray-50 selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-8 transform hover:scale-105 transition-transform duration-300">
                <a href="/">
                    <img src="{{ asset('icons/reso.png') }}" alt="Reso Logo" class="w-24 h-auto drop-shadow-md">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-10 py-10 bg-white shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 sm:rounded-[2rem]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
