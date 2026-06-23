<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connecting to Spotify - {{ config('app.name', 'Reso') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-white antialiased flex flex-col items-center justify-center min-h-screen relative overflow-hidden">
    <!-- Ambient backdrops -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-green-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col items-center text-center px-4 max-w-md">
        <!-- Modern music pulse animation -->
        <div class="flex items-end justify-center space-x-1.5 h-12 mb-8">
            <span class="w-1.5 bg-green-500 rounded-full animate-bounce" style="animation-duration: 0.8s; height: 100%;"></span>
            <span class="w-1.5 bg-purple-500 rounded-full animate-bounce" style="animation-duration: 1.1s; height: 75%;"></span>
            <span class="w-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-duration: 0.9s; height: 90%;"></span>
            <span class="w-1.5 bg-green-500 rounded-full animate-bounce" style="animation-duration: 0.7s; height: 60%;"></span>
            <span class="w-1.5 bg-purple-500 rounded-full animate-bounce" style="animation-duration: 1.2s; height: 80%;"></span>
        </div>

        <!-- Custom Snackbar Notification -->
        <div class="bg-white/10 dark:bg-zinc-900/80 backdrop-blur-xl border border-white/10 p-5 rounded-2xl shadow-2xl flex items-center space-x-4 max-w-sm animate-pulse">
            <div class="flex-shrink-0 bg-green-500/20 text-[#1DB954] p-2.5 rounded-xl">
                <!-- Spotify Icon -->
                <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.84.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
            </div>
            <div class="text-left">
                <p class="font-bold text-white text-sm">Connecting to Spotify...</p>
                <p class="text-xs text-gray-400 mt-0.5 font-medium">Taking longer than usual, retrying sync</p>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-6 tracking-wide">Please do not close this window.</p>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "{{ route('social.redirect', 'spotify') }}";
        }, 2000);
    </script>
</body>
</html>
