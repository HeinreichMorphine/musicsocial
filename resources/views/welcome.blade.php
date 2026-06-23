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
        body { background-color: #000000; color: #ffffff; }
        .hero-bg {
            background: radial-gradient(circle at top center, rgba(29, 185, 84, 0.15) 0%, rgba(0, 0, 0, 1) 100%);
        }
        .border-glow {
            box-shadow: 0 0 40px rgba(29, 185, 84, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased selection:bg-brand selection:text-white">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 top-0 bg-black/80 backdrop-blur-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('icons/reso.png') }}" class="h-8 w-auto object-contain brightness-0 invert" alt="Reso Logo">
                    <span class="font-bold text-xl tracking-tight text-white">Reso</span>
                </div>
                <div class="flex items-center space-x-6">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full bg-white text-black text-sm font-bold hover:bg-gray-200 transition">
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
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden hero-bg min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Text Content -->
                <div class="text-left max-w-2xl">
                    <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6 tracking-tight">
                        Music discovery, <br>
                        <span class="text-brand">humanized.</span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-400 mb-10 leading-relaxed">
                        Drop the algorithm. Connect with listeners who share your exact music taste and discover your next favorite track through real recommendations.
                    </p>

                    @if (Route::has('login'))
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-brand text-black font-bold hover:bg-[#1ed760] transition text-center">
                                    Open Web Player
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-brand text-black font-bold hover:bg-[#1ed760] transition text-center">
                                        Create Account
                                    </a>
                                @endif
                                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-full bg-transparent border border-white/20 text-white font-bold hover:bg-white/10 transition text-center">
                                    Log in
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>

                <!-- App Mockup -->
                <div class="relative max-w-md mx-auto w-full lg:ml-auto">
                    <div class="absolute -inset-0.5 bg-brand/20 rounded-2xl blur-2xl"></div>
                    
                    <div class="bg-card border border-white/10 rounded-2xl shadow-2xl p-6 relative z-10 border-glow transform lg:rotate-y-[-5deg] lg:rotate-x-[5deg] transition-transform duration-500 hover:rotate-0">
                        <!-- Header -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-800 overflow-hidden">
                                <img src="https://i.pravatar.cc/150?img=33" alt="User" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="text-white font-bold text-sm">Marcus</div>
                                <div class="text-brand text-xs font-semibold px-2 py-0.5 bg-brand/10 rounded-full mt-0.5 inline-block border border-brand/20">94% Taste Match</div>
                            </div>
                            <div class="ml-auto text-gray-500 text-xs">2h ago</div>
                        </div>

                        <!-- Post Body -->
                        <p class="text-gray-200 text-sm mb-4 leading-relaxed">
                            Been gatekeeping this one for too long. The bassline at the bridge is absolutely ridiculous. Highly recommend.
                        </p>

                        <!-- Track Card -->
                        <div class="bg-darker rounded-xl p-4 flex items-center gap-4 border border-white/5 relative overflow-hidden group">
                            <!-- Background glow -->
                            <div class="absolute inset-0 bg-gradient-to-r from-brand/10 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>

                            <div class="w-16 h-16 rounded-md bg-gray-800 overflow-hidden shrink-0 relative z-10">
                                <img src="https://picsum.photos/seed/album1/200" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0 relative z-10">
                                <div class="text-white font-bold truncate">Neon Sequence</div>
                                <div class="text-gray-400 text-sm truncate">Night Drive</div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center shrink-0 relative z-10 shadow-[0_0_15px_rgba(29,185,84,0.4)]">
                                <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-6 mt-5 text-gray-500">
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
    <section class="py-24 bg-darker border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div>
                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Share Your Rotation</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Post the tracks you can't stop playing. Build your profile and let your taste speak for itself.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div>
                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Find Your Neighbors</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Our engine analyzes your Spotify history to match you with users who share your exact niche.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div>
                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Curate Together</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Save tracks directly to your Spotify playlists. Collaborate and build a library with your network.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black border-t border-white/10 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <img src="{{ asset('icons/reso.png') }}" class="h-6 w-auto object-contain brightness-0 invert opacity-50" alt="Reso">
                <span class="font-bold text-gray-500">Reso</span>
            </div>
            <div class="text-gray-600 text-sm">
                &copy; {{ date('Y') }} Reso Systems. Open Source MIT License.
            </div>
        </div>
    </footer>
</body>
</html>
