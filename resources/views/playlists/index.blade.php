<x-app-layout pageTitle="Playlists">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <!-- Left Sidebar -->
            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-24 pt-4">
                    <div class="bg-white/60 dark:bg-black border border-white/40 dark:border-white/5 rounded-3xl p-4 shadow-2xl flex flex-col gap-4">
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 space-y-8">
                <!-- Pending Invites Section -->
                @if($invites->isNotEmpty())
                <div class="bg-indigo-900/20 border border-indigo-500/30 rounded-3xl p-6">
                    <h3 class="text-xl font-bold text-indigo-400 mb-4">Pending Invitations</h3>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($invites as $invite)
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-4 flex items-center justify-between shadow-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <h4 class="font-bold text-lg text-gray-900 dark:text-white">{{ $invite->name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Invited by {{ $invite->collaborators->where('role', 'owner')->first()->user->name ?? 'Unknown' }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <form action="{{ route('playlists.accept', $invite) }}" method="POST">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-xl font-medium text-sm transition shadow">Accept</button>
                                </form>
                                <form action="{{ route('playlists.decline', $invite) }}" method="POST">
                                    @csrf
                                    <button class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl font-medium text-sm transition shadow">Decline</button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Active Playlists -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center px-2 gap-4">
                    <h2 class="text-3xl font-extrabold tracking-tight dark:text-white">Your Playlists</h2>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('playlists.import.index') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2 relative">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm5.508 17.302c-.216.354-.675.465-1.028.249-2.815-1.722-6.36-2.112-10.537-1.157-.403.093-.811-.158-.905-.562-.093-.404.159-.812.562-.905 4.577-1.047 8.508-.602 11.659 1.326.354.216.465.675.249 1.028zm1.474-3.264c-.273.443-.852.583-1.295.31-3.222-1.98-8.136-2.557-11.947-1.4c-.5.152-1.025-.13-1.177-.63-.153-.5.13-1.025.63-1.177 4.357-1.322 9.774-.678 13.482 1.6 0 .001.442.274.707.697zm.128-3.413C15.111 8.217 8.513 7.994 4.697 9.151c-.604.183-1.246-.164-1.428-.767-.183-.604.164-1.246.767-1.428 4.38-1.328 11.666-1.066 16.326 1.7 0 .001 1.107.657.828 1.488-.28.831-1.08 1.141-1.08 1.141z"/></svg>
                            <span class="hidden sm:inline">Import Spotify</span>
                            @if(Auth::user()->spotify_token === null)
                                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                </span>
                            @endif
                        </a>
                        <button x-data @click="$dispatch('open-modal', 'create-playlist')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span class="hidden sm:inline">New Playlist</span>
                        </button>
                    </div>
                </div>

                <!-- Onboarding Explainer Banner Container -->
                <div x-data="{ isCollapsed: localStorage.getItem('playlistOnboardingCollapsed') === 'true' }" class="space-y-4">
                    <!-- Collapsed State Header -->
                    <div x-show="isCollapsed" 
                         @click="isCollapsed = false; localStorage.setItem('playlistOnboardingCollapsed', 'false')"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="relative bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-3.5 cursor-pointer hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 transition-all duration-300 flex items-center justify-between shadow-md group/collapsed"
                    >
                        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300 text-sm">
                            <span class="text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </span>
                            <span class="font-medium group-hover/collapsed:text-gray-900 dark:group-hover/collapsed:text-white transition-colors">How Playlists Work: Curation, Recommendations & Spotify Syncing</span>
                        </div>
                        <span class="text-gray-400 dark:text-gray-500 group-hover/collapsed:text-gray-600 dark:group-hover/collapsed:text-zinc-300 transition-colors flex items-center gap-1.5 text-xs font-semibold">
                            Expand
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </span>
                    </div>

                    <!-- Expanded State (Full Banner) -->
                    <div x-show="!isCollapsed"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
                         class="relative bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-xl overflow-hidden group/banner"
                    >
                        <!-- Background ambient glow -->
                        <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none transition-all duration-700 group-hover/banner:bg-indigo-500/10 dark:group-hover/banner:bg-indigo-500/20"></div>
                        <div class="absolute -left-12 -top-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none transition-all duration-700 group-hover/banner:bg-emerald-500/10"></div>

                        <!-- Collapse button in top right -->
                        <button @click="isCollapsed = true; localStorage.setItem('playlistOnboardingCollapsed', 'true')" 
                                class="absolute top-4 right-4 text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/50 p-1.5 rounded-lg transition-all duration-200 z-10 flex items-center gap-1.5 text-xs font-semibold"
                                title="Collapse Overview"
                        >
                            Collapse
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7-7 7 7" />
                            </svg>
                        </button>

                        <div class="relative z-10">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Get the most out of Playlists</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-2xl">Collaborate, shape your recommendation DNA, and take your music anywhere with Reso and Spotify synchronization.</p>

                            <!-- Pillars Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Pillar 1: Curate Shared Vibes -->
                                <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800 rounded-2xl p-5 hover:border-indigo-500/30 dark:hover:border-indigo-500/30 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all duration-300 flex flex-col items-start">
                                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2.5 rounded-xl mb-4 transition-transform duration-300 hover:scale-110">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Curate Shared Vibes</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Easily build and organize soundtracks with your friends. Invite mutual followers to collaborate and curate the perfect playlist together in real time.</p>
                                </div>

                                <!-- Pillar 2: Shape Your Recommendations -->
                                <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800 rounded-2xl p-5 hover:border-fuchsia-500/30 dark:hover:border-fuchsia-500/30 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all duration-300 flex flex-col items-start">
                                    <div class="bg-fuchsia-50 dark:bg-fuchsia-900/20 border border-fuchsia-100 dark:border-fuchsia-500/20 text-fuchsia-600 dark:text-fuchsia-400 p-2.5 rounded-xl mb-4 transition-transform duration-300 hover:scale-110">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path>
                                            <path d="m5 3 1 2.5L8.5 6 6 7 5 9.5 4 7 1.5 6 4 5 5 3Z"></path>
                                            <path d="m19 17 1 2.5 2.5.5-2.5 1-1 2.5-1-2.5-2.5-1 2.5-1 1-2.5Z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Shape Your Recommendations</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">The music you add to your playlists teaches the platform what you love. It automatically refines your personal music taste to recommend better matches on your Discovery feed.</p>
                                </div>

                                <!-- Pillar 3: Sync with Spotify -->
                                <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800 rounded-2xl p-5 hover:border-emerald-500/30 dark:hover:border-emerald-500/30 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all duration-300 flex flex-col items-start">
                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2.5 rounded-xl mb-4 transition-transform duration-300 hover:scale-110">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm5.508 17.302c-.216.354-.675.465-1.028.249-2.815-1.722-6.36-2.112-10.537-1.157-.403.093-.811-.158-.905-.562-.093-.404.159-.812.562-.905 4.577-1.047 8.508-.602 11.659 1.326.354.216.465.675.249 1.028zm1.474-3.264c-.273.443-.852.583-1.295.31-3.222-1.98-8.136-2.557-11.947-1.4c-.5.152-1.025-.13-1.177-.63-.153-.5.13-1.025.63-1.177 4.357-1.322 9.774-.678 13.482 1.6 0 .001.442.274.707.697zm.128-3.413C15.111 8.217 8.513 7.994 4.697 9.151c-.604.183-1.246-.164-1.428-.767-.183-.604.164-1.246.767-1.428 4.38-1.328 11.666-1.066 16.326 1.7 0 .001 1.107.657.828 1.488-.28.831-1.08 1.141-1.08 1.141z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Sync with Spotify</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Seamlessly import your playlists from Spotify, or export your curated Reso playlists back to your Spotify library to take your music anywhere.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($playlists->isEmpty())
                <div class="text-center py-16 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-gray-200 dark:border-gray-700/50 border-dashed backdrop-blur-sm">
                    <div class="bg-gray-100 dark:bg-gray-800/50 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-xl font-medium text-gray-600 dark:text-gray-200">No playlists yet</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">Create a playlist, invite your peers, and let the algorithm map your musical tastes together automatically.</p>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($playlists as $playlist)
                    <div id="playlist-card-{{ $playlist->id }}" class="relative group">
                        <a href="{{ route('playlists.show', $playlist) }}" class="block bg-white dark:bg-black rounded-3xl overflow-hidden shadow-sm hover:shadow-xl border border-gray-100 dark:border-white/10 hover:border-indigo-500/50 transition-all duration-300 transform group-hover:-translate-y-1">
                            <div class="h-44 bg-gradient-to-br from-indigo-900 to-gray-900 relative">
                                @if($playlist->cover_image_url)
                                    <img src="{{ $playlist->cover_image_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $playlist->name }}">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center z-10">
                                        <div class="px-6 text-center w-full">
                                            <h4 class="text-white text-xl font-black opacity-30 group-hover:opacity-60 group-hover:scale-110 transition-all duration-500 m-0 leading-tight line-clamp-2">
                                                {{ $playlist->name }}
                                            </h4>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5 text-center">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-500 transition-colors">{{ $playlist->name }}</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 line-clamp-2 min-h-[40px]">{{ $playlist->description ?? 'No description.' }}</p>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                                    <div class="flex -space-x-2 overflow-hidden">
                                        @foreach($playlist->collaborators->where('status', 'accepted')->take(5) as $collab)
                                            <x-user-avatar :user="$collab->user" class="h-8 w-8 ring-2 ring-white dark:ring-black" title="{{ $collab->user->name }}" />
                                        @endforeach
                                        @if($playlist->collaborators->where('status', 'accepted')->count() > 5)
                                            <div class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white dark:ring-black bg-gray-100 dark:bg-gray-800 text-[10px] font-bold text-gray-500">
                                                +{{ $playlist->collaborators->where('status', 'accepted')->count() - 5 }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if(str_contains($playlist->description, 'Imported'))
                                            <span class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full uppercase tracking-wider">Imported</span>
                                        @endif
                                        <span class="text-[10px] font-bold text-indigo-500 bg-indigo-500/10 px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $playlist->songs()->count() }} tracks</span>
                                    </div>
                                </div>
                            </div>
                        </a>

                        @php
                            $userCollab = $playlist->collaborators->where('user_id', auth()->id())->first();
                        @endphp

                        @if($userCollab && $userCollab->role === 'owner')
                        <div class="absolute top-4 right-4 flex gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300">
                            <a href="{{ route('playlists.edit', $playlist) }}" class="bg-white/90 dark:bg-black/90 p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 shadow-lg transition-colors border border-gray-100 dark:border-white/10" title="Edit Playlist">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <button type="button" 
                                onclick="handlePlaylistDelete('{{ $playlist->id }}', '{{ route('playlists.destroy', $playlist) }}')"
                                class="bg-white/90 dark:bg-black/90 p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 shadow-lg transition-colors border border-gray-100 dark:border-white/10" title="Delete Playlist">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24 pt-4">
                    <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                    <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                </div>
            </div>
        </div>
    </div>

    <!-- Create Playlist Modal -->
    <x-modal name="create-playlist" focusable>
        <form method="post" action="{{ route('playlists.store') }}" class="p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-2xl">
            @csrf
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">New Playlist</h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Playlist Name</label>
                    <input type="text" name="name" required class="block w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (Optional)</label>
                    <textarea name="description" rows="3" class="block w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4 transition"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancel</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg transition">Create Playlist</button>
            </div>
        </form>
    </x-modal>

    <script>
        const handlePlaylistDelete = async (playlistId, deleteUrl) => {
            if (!confirm("Are you sure you want to permanently delete this playlist?")) return;

            try {
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload(); 
                } else {
                    alert("Error deleting playlist: " + result.message);
                }
            } catch (error) {
                console.error("Deletion request failed:", error);
            }
        };
    </script>
</x-app-layout>

