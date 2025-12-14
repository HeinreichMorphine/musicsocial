<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MusicSocial') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased font-sans min-h-screen flex flex-col selection:bg-custom-mid-blue selection:text-white">

    <div class="flex-grow flex items-center justify-center relative">
        <!-- Background Elements (Subtle) -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 opacity-30">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-100 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-[50%] h-[50%] bg-pink-100 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-4xl w-full px-6 text-center">
            
            <!-- Logo -->
            <div class="flex justify-center mb-10">
                <img src="{{ asset('icons/reso.png') }}" alt="Reso Logo" class="h-32 w-auto animate-fade-in-down">
            </div>

            <!-- Headlines -->
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight text-gray-900 mb-6 drop-shadow-sm">
                Connect Through <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Sound</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-gray-600 mb-10 max-w-2xl mx-auto leading-relaxed">
                Discover your musical identity, find your 
                <span class="font-bold text-gray-800">Taste Twins</span>, 
                and share the songs that define you.
            </p>

            <!-- Buttons -->
            @if (Route::has('login'))
                <div class="flex flex-col sm:flex-row justify-center gap-4 animate-fade-in-up">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 bg-custom-mid-blue hover:bg-custom-dark-blue text-white font-bold rounded-full text-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-8 py-3.5 bg-custom-mid-blue hover:bg-custom-dark-blue text-white font-bold rounded-full text-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-8 py-3.5 bg-white text-gray-700 font-bold rounded-full text-lg border-2 border-gray-200 hover:border-gray-400 hover:text-gray-900 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-1">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-6 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} MusicSocial. All rights reserved.
    </footer>

    <!-- Custom Animation CSS -->
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        .animate-fade-in-down {
            animation: fadeInDown 0.8s ease-out;
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out 0.2s backwards;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
