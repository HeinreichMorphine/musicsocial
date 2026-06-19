<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Reso') }} - Connect Through Sound</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                        casual: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        'custom-mid-blue': '#2563EB',
                        'custom-dark-blue': '#1E40AF',
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Scale the entire UI down dynamically on small mobile devices */
        @media (max-width: 480px) {
            html {
                font-size: clamp(10px, 3.75vw, 16px);
            }
        }
        .border-inset {
            box-shadow: inset 2px 2px 0px 0px #808080, inset -2px -2px 0px 0px #ffffff;
        }
        .hero-gradient {
            background: radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.08) 0%, rgba(255, 255, 255, 0) 70%),
                        linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
        }

        /* JEWEL CASE CSS EFFECT - Applied to the Album Art */
        .jewel-case {
            position: relative;
            background: #fff;
            padding: 2px;
            box-shadow: 
                inset 1px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px 1px 0px rgba(255, 255, 255, 0.5),
                inset 12px 0px 5px -2px rgba(0, 0, 0, 0.05),
                inset 12px 0px 2px -2px rgba(255, 255, 255, 0.8),
                2px 2px 5px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            border-left: 1px solid rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .jewel-case::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 40%, rgba(255,255,255,0) 41%, rgba(255,255,255,0) 100%);
            pointer-events: none;
            z-index: 10;
        }
        .jewel-case::before {
            content: '';
            position: absolute; left: 5px; top: 0; bottom: 0; width: 2px;
            background: rgba(0,0,0,0.05); z-index: 5;
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 selection:bg-blue-100 selection:text-blue-900">

    <!-- Navbar -->
    <nav x-data="{ scrolled: false }" 
         @scroll.window="scrolled = (window.pageYOffset > 20)"
         :class="{ 'bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100': scrolled, 'bg-transparent': !scrolled }"
         class="fixed w-full z-50 transition-all duration-300 top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('icons/reso.png') }}" class="h-8 w-auto object-contain" alt="Reso Logo">
                    <span class="font-display font-bold text-2xl tracking-tight text-gray-900">Reso</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#community" class="text-sm font-bold text-gray-600 hover:text-custom-mid-blue transition tracking-wider uppercase">Network</a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-custom-mid-blue hover:text-custom-dark-blue">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-900 hover:text-custom-mid-blue">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    Join Now
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden hero-gradient">
        <!-- Animated Blobs -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 left-0 -ml-20 -mt-20 w-72 h-72 bg-purple-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="font-casual text-4xl sm:text-5xl md:text-7xl font-extrabold text-gray-900 leading-tight mb-8">
                    Social Audio, <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-custom-mid-blue to-purple-600">Built for Connection.</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-500 mb-10 max-w-2xl mx-auto leading-relaxed">
                    Reso brings the human element back to music discovery. Find your "Taste Neighbors" and discover music through genuine peer-to-peer recommendations.
                </p>
            </div>

            <!-- WINDOW OS MOCKUP -->
            <div class="mt-20 relative max-w-6xl mx-auto">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur opacity-10"></div>
                
                <!-- The Window Container -->
                <div class="bg-gray-100 rounded-xl shadow-2xl overflow-hidden border border-gray-300 relative z-10">
                    
                    <!-- Title Bar -->
                    <div class="bg-gray-200 border-b border-gray-300 h-10 flex items-center px-4 justify-between">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-400 border border-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400 border border-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400 border border-green-500"></div>
                        </div>
                        <div class="flex-1 px-4">
                            <div class="bg-white border border-gray-300 rounded text-center text-xs text-gray-500 py-1 max-w-md mx-auto flex justify-center items-center gap-2">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                reso.app/explore
                            </div>
                        </div>
                    </div>

                    <!-- Browser Body (App Preview) -->
                    <div class="bg-gray-50 p-4 md:p-8 grid grid-cols-12 gap-8 h-[500px] md:h-[600px] overflow-hidden relative">
                        <!-- Fade Out at bottom -->
                        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-gray-50 to-transparent z-20 pointer-events-none"></div>

                        <!-- Left Nav Mockup -->
                        <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                             <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-sm space-y-4">
                                <div class="h-6 w-6 bg-custom-mid-blue rounded-full mb-4"></div>
                                <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                                <div class="h-2 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-2 bg-gray-200 rounded w-3/4"></div>
                             </div>
                        </div>

                        <!-- Main Feed Area -->
                        <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 space-y-6">
                            
                            <!-- Tabs -->
                            <div class="flex space-x-6 border-b border-gray-200 mb-6">
                                <div class="pb-3 text-lg font-bold text-gray-400">Following</div>
                                <div class="pb-3 text-lg font-bold text-gray-900 border-b-2 border-custom-mid-blue">Explore</div>
                            </div>

                            <!-- Share Card (Exact Replica) -->
                            <div class="bg-white/60 backdrop-blur-md rounded-3xl shadow-sm border border-white/50 p-6">
                                <!-- Header -->
                                <div class="flex space-x-3 mb-4">
                                    <div class="h-14 w-14 rounded-full bg-gray-200 border-2 border-white shadow-sm shrink-0 overflow-hidden">
                                        <img src="https://i.pravatar.cc/150?u=alex" alt="User" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">Alex M.</span>
                                            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">Taste Neighbor</span>
                                        </div>
                                        <span class="text-gray-500 text-sm">2 hours ago</span>
                                    </div>
                                </div>

                                <!-- Caption -->
                                <p class="mb-4 text-gray-800 text-lg leading-relaxed">
                                    The production on this track is absolutely insane. Specifically the layering at 2:34... genuine masterpiece.
                                </p>

                                <!-- Song Card (With Jewel Case) -->
                                <div class="relative overflow-hidden rounded-3xl p-6 group mt-4">
                                    <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-90 transform scale-110" style="background-image: url('https://picsum.photos/seed/electronic/400');"></div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                                    
                                    <div class="relative flex items-center space-x-6">
                                        <!-- Jewel Case Applied Here -->
                                        <div class="jewel-case w-24 h-24 sm:w-32 sm:h-32 shrink-0 transform rotate-1 group-hover:rotate-0 transition-transform duration-500">
                                            <img src="https://picsum.photos/seed/electronic/400" class="w-full h-full object-cover">
                                        </div>

                                        <div class="flex-1 min-w-0 text-white">
                                            <h3 class="text-2xl font-bold truncate drop-shadow-md">Midnight City</h3>
                                            <p class="text-lg text-gray-200 truncate drop-shadow-sm">M83</p>
                                            <div class="flex gap-3 mt-3 opacity-90">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center p-1.5"><svg fill="currentColor" viewBox="0 0 24 24" class="text-black"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.48.66.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg></div>
                                                <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center p-1.5"><svg fill="white" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-5 border-t border-gray-100/50 pt-3 grid grid-cols-4 gap-2">
                                    <div class="flex items-center justify-center gap-2 py-2 rounded-xl hover:bg-pink-50 text-gray-500 hover:text-pink-500 cursor-pointer transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                        <span class="font-bold text-sm">24</span>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 py-2 rounded-xl hover:bg-gray-100 text-gray-500 hover:text-red-500 cursor-pointer transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" /><path d="M12 13l-1-1 2-2-3-3 2-2" /></svg>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 py-2 rounded-xl hover:bg-blue-50 text-gray-500 hover:text-custom-mid-blue cursor-pointer transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.056 3 11.625c0 4.291 3.52 7.846 8.25 8.142.026.002.051.002.076.002Z" /></svg>
                                        <span class="font-bold text-sm">8</span>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 py-2 rounded-xl hover:bg-yellow-50 text-gray-500 hover:text-yellow-500 cursor-pointer transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0111.186 0z" /></svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Another Faded Share Card Below -->
                            <div class="bg-white/40 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 p-6 opacity-50">
                                <div class="flex space-x-3">
                                    <div class="h-10 w-10 rounded-full bg-gray-200"></div>
                                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar Mockup -->
                        <div class="hidden lg:block lg:col-span-3">
                            <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-sm">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Suggested People</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-purple-100 rounded-full"></div>
                                        <div>
                                            <div class="h-2.5 bg-gray-800 w-24 rounded mb-1"></div>
                                            <div class="text-[10px] text-green-600 font-bold">98% Taste Match</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full"></div>
                                        <div>
                                            <div class="h-2.5 bg-gray-800 w-20 rounded mb-1"></div>
                                            <div class="text-[10px] text-green-600 font-bold">85% Taste Match</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>



     <!-- Community Section (Revised Visual) -->
    <section id="community" class="py-24 bg-gray-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-16">
                 <!-- Visual -->
                 <div class="w-full md:w-1/2">
                    <div class="bg-gray-800 rounded-xl p-8 shadow-2xl border border-gray-700 relative">
                        <!-- Chat Bubble Mockup -->
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex-shrink-0"></div>
                                <div class="bg-gray-700 p-3 rounded-xl rounded-bl-sm w-full">
                                    <p class="font-semibold text-xs mb-1 text-blue-300">Sarah J.</p>
                                    <p class="text-sm text-gray-200">I've been looking for this track for years! Thanks for sharing.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 flex-row-reverse">
                                <div class="w-10 h-10 rounded-full bg-purple-500 flex-shrink-0"></div>
                                <div class="bg-blue-600 p-3 rounded-xl rounded-br-sm w-full text-white">
                                    <p class="font-semibold text-xs mb-1 text-blue-200">You</p>
                                    <p class="text-sm">Glad you like it! It's from their 2004 live set.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text Content -->
                <div class="w-full md:w-1/2">
                    <h2 class="font-display text-4xl font-bold mb-6">Music is Social.</h2>
                    <p class="text-gray-400 text-lg mb-8 leading-relaxed">
                        Reso is built on the belief that the best way to find new music is through the people who love it. 
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                             <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center mt-1 mr-4 flex-shrink-0 text-xs font-bold">1</div>
                            <div>
                                <h4 class="font-bold text-white">Share Your World</h4>
                                <p class="text-gray-500 text-sm">Post the tracks that define your moments.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                             <div class="w-6 h-6 rounded-full bg-purple-600 flex items-center justify-center mt-1 mr-4 flex-shrink-0 text-xs font-bold">2</div>
                            <div>
                                <h4 class="font-bold text-white">Find Your Tribe</h4>
                                <p class="text-gray-500 text-sm">Connect with people on your wavelength.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1 mr-4 flex-shrink-0 text-xs font-bold">3</div>
                            <div>
                                <h4 class="font-bold text-white">Expansive Discovery</h4>
                                <p class="text-gray-500 text-sm">Break the echo chamber bubble.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <img src="{{ asset('icons/reso.png') }}" class="h-6 w-auto object-contain grayscale opacity-80" alt="Reso">
                <span class="font-bold text-gray-900">Reso</span>
            </div>
            <div class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} Reso Systems. Open Source MIT License.
            </div>
        </div>
    </footer>
</body>
</html>
