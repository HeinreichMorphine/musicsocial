<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reso - Discover music through real people</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Dark Mode Logic
        const theme = localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', theme === 'dark');
        
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: '#1DB954', // Spotify Green for accent
                        dark: '#000000',
                        darker: '#121212',
                        card: '#181818',
                    }
                }
            }
        }
    </script>
    <style>
        .hero-bg-dark {
            background: radial-gradient(circle at top center, rgba(29, 185, 84, 0.15) 0%, rgba(0, 0, 0, 1) 100%);
        }
        .hero-bg-light {
            background: radial-gradient(circle at top center, rgba(37, 99, 235, 0.08) 0%, rgba(255, 255, 255, 0) 70%),
                        linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
        }
        .border-glow-dark {
            box-shadow: 0 0 40px rgba(29, 185, 84, 0.1);
        }
        .border-glow-light {
            box-shadow: 0 0 40px rgba(29, 185, 84, 0.2);
        }
    </style>
</head>
<body class="font-sans antialiased selection:bg-brand selection:text-white bg-slate-50 dark:bg-black text-slate-900 dark:text-white transition-colors duration-200">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 top-0 bg-slate-50/80 dark:bg-black/80 backdrop-blur-md border-b border-gray-200 dark:border-white/10 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('icons/reso.png') }}" class="h-8 w-auto object-contain dark:brightness-0 dark:invert transition" alt="Reso Logo">
                    <span class="font-bold text-xl tracking-tight">Reso</span>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition p-2 rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
                        <!-- Sun Icon (shows in dark mode) -->
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <!-- Moon Icon (shows in light mode) -->
                        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-bold hover:from-blue-700 hover:to-purple-700 transition">
                                    Sign up
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden min-h-screen flex items-center">
        <!-- Dynamic Background Class -->
        <div class="absolute inset-0 block dark:hidden hero-bg-light -z-10"></div>
        <div class="absolute inset-0 hidden dark:block hero-bg-dark -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Text Content -->
                <div class="text-left max-w-2xl">
                    <h1 class="text-5xl md:text-7xl font-extrabold leading-tight mb-6 tracking-tight">
                        Music discovery, <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:text-brand dark:bg-none dark:bg-transparent">humanized.</span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 mb-10 leading-relaxed transition-colors">
                        Drop the algorithm. Connect with listeners who share your exact music taste and discover your next favorite track through real recommendations.
                    </p>

                    @if (Route::has('login'))
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-brand text-white dark:text-black font-bold hover:bg-[#1ed760] transition text-center shadow-lg hover:shadow-xl">
                                    Open Web Player
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold hover:from-blue-700 hover:to-purple-700 transition text-center shadow-lg hover:shadow-xl">
                                        Create Account
                                    </a>
                                @endif
                                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-transparent border border-gray-300 dark:border-white/20 text-gray-800 dark:text-white font-bold hover:bg-gray-50 dark:hover:bg-white/10 transition text-center">
                                    Log in
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>

                <!-- App Mockup -->
                <div class="relative max-w-md mx-auto w-full lg:ml-auto">
                    <div class="absolute -inset-0.5 bg-brand/10 dark:bg-brand/20 rounded-2xl blur-2xl transition"></div>
                    
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl p-6 relative z-10 border-glow-light dark:border-glow-dark transform lg:rotate-y-[-5deg] lg:rotate-x-[5deg] transition-all duration-500 hover:rotate-0">
                        <!-- Header -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden shrink-0">
                                <img src="https://i.pravatar.cc/150?img=33" alt="User" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="font-bold text-sm">Marcus</div>
                                <div class="text-brand text-xs font-semibold px-2 py-0.5 bg-brand/10 rounded-full mt-0.5 inline-block border border-brand/20">94% Taste Match</div>
                            </div>
                            <div class="ml-auto text-gray-400 dark:text-gray-500 text-xs">2h ago</div>
                        </div>

                        <!-- Post Body -->
                        <p class="text-gray-700 dark:text-gray-200 text-sm mb-4 leading-relaxed transition-colors">
                            Been gatekeeping this one for too long. The bassline at the bridge is absolutely ridiculous. Highly recommend.
                        </p>

                        <!-- Track Card -->
                        <div class="mt-3 md:mt-4 relative rounded-2xl md:rounded-3xl p-4 md:p-6 group">
                            <!-- Background blur/gradient wrapper -->
                            <div class="absolute inset-0 rounded-2xl md:rounded-3xl overflow-hidden pointer-events-none">
                                <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-90 transform scale-110 transition-transform duration-700 group-hover:scale-125" style="background-image: url('https://picsum.photos/seed/album1/400');"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            </div>
                            <div class="relative flex items-center space-x-4 md:space-x-6 z-10">
                                <img src="https://picsum.photos/seed/album1/400" alt="Album Art" class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl md:rounded-2xl shadow-xl transition-transform duration-300 group-hover:scale-105 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xl md:text-2xl font-bold text-white truncate drop-shadow-md">Neon Sequence</p>
                                    <p class="text-base md:text-lg text-gray-200 truncate drop-shadow-sm">Night Drive</p>
                                    
                                    <div class="flex items-center space-x-3 mt-2 md:mt-3">
                                        <button type="button" title="Play track" class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(30,215,96,0.6)] relative">
                                            <svg class="w-8 h-8 drop-shadow-lg" fill="#1DB954" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                        </button>
                                        
                                        <div class="relative inline-block">
                                            <button type="button" title="Add to Playlist" class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.4)] bg-white/20 rounded-full p-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </button>
                                        </div>

                                        <a href="#" class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,0,0,0.6)]">
                                            <svg class="w-8 h-8 drop-shadow-lg" fill="#FF0000" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-6 mt-5 text-gray-400 dark:text-gray-500 transition-colors">
                            <div class="flex items-center gap-2 hover:text-pink-500 cursor-pointer transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span class="text-xs font-semibold">12</span>
                            </div>
                            <div class="flex items-center gap-2 hover:text-blue-500 cursor-pointer transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                <span class="text-xs font-semibold">3</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-gray-50 dark:bg-darker border-t border-gray-100 dark:border-white/5 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div>
                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 flex items-center justify-center mb-6 shadow-sm transition-colors">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Share Your Rotation</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed transition-colors">
                        Post the tracks you can't stop playing. Build your profile and let your taste speak for itself.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div>
                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 flex items-center justify-center mb-6 shadow-sm transition-colors">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Find Your Neighbors</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed transition-colors">
                        Our engine analyzes your Spotify history to match you with users who share your exact niche.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div>
                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 flex items-center justify-center mb-6 shadow-sm transition-colors">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Curate Together</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed transition-colors">
                        Save tracks directly to your Spotify playlists. Collaborate and build a library with your network.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-black border-t border-gray-200 dark:border-white/10 py-12 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <img src="{{ asset('icons/reso.png') }}" class="h-6 w-auto object-contain dark:brightness-0 dark:invert opacity-50 grayscale transition" alt="Reso">
                <span class="font-bold text-gray-500">Reso</span>
            </div>
            <div class="text-gray-400 dark:text-gray-600 text-sm transition-colors">
                &copy; {{ date('Y') }} Reso Systems. Open Source MIT License.
            </div>
        </div>
    </footer>
</body>
</html>
