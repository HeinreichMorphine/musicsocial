<x-app-layout pageTitle="Edit Playlist: {{ $playlist->name }}">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">
            
            <!-- Left Sidebar -->
            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-24 pt-4">
                    <div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl">
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 space-y-6">
                <a href="{{ route('playlists.show', $playlist) }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors mb-2 group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Playlist
                </a>

                <div class="bg-white dark:bg-black rounded-[2.5rem] p-8 border border-gray-100 dark:border-white/10 shadow-sm relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-purple-500/5 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-4 bg-indigo-600/10 rounded-2xl">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Edit Playlist</h2>
                                <p class="text-gray-500 dark:text-gray-400">Update your playlist's identity.</p>
                            </div>
                        </div>

                        <form action="{{ route('playlists.update', $playlist->id) }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Playlist Name</label>
                                <input type="text" name="name" id="name" 
                                       class="w-full bg-gray-50 dark:bg-black border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl px-5 py-4 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm font-bold" 
                                       value="{{ old('name', $playlist->name) }}" required>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Description</label>
                                <textarea name="description" id="description" rows="4" 
                                          class="w-full bg-gray-50 dark:bg-black border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl px-5 py-4 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm" 
                                          placeholder="Tell us more about this vibe...">{{ old('description', $playlist->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4">
                                <a href="{{ route('playlists.show', $playlist->id) }}" class="px-6 py-3 rounded-2xl text-gray-500 hover:text-gray-700 dark:hover:text-white font-bold transition">Cancel</a>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-black px-8 py-3 rounded-2xl shadow-xl shadow-indigo-500/20 transition transform hover:-translate-y-1 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
</x-app-layout>
