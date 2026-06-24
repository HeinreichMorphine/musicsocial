<x-app-layout pageTitle="{{ $user->name }}'s Taste DNA">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <!-- Left Sidebar -->
                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-0 pt-4">
                        <div class="bg-white/60 dark:bg-black border border-white/40 dark:border-white/5 rounded-3xl p-4 shadow-2xl flex flex-col gap-4">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                    
                    <!-- Profile Header (Shared) -->
                    @include('profile.partials.header', ['user' => $user, 'badge' => $badge ?? null])

                    <!-- Taste DNA Content -->
                    <div class="space-y-6">
                        
                        <!-- Genre DNA Card -->
                        <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-gray-100 dark:border-white/10">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                                    <span class="text-2xl">🎵</span>
                                </span>
                                Genre DNA
                            </h3>
                            
                            @if(count($genreDna) > 0)
                                @php
                                    $colors = [
                                        ['text' => 'text-purple-500', 'bg' => 'bg-purple-500', 'gradient' => 'from-purple-400 to-purple-600'],
                                        ['text' => 'text-blue-500', 'bg' => 'bg-blue-500', 'gradient' => 'from-blue-400 to-blue-600'],
                                        ['text' => 'text-pink-500', 'bg' => 'bg-pink-500', 'gradient' => 'from-pink-400 to-pink-600'],
                                        ['text' => 'text-green-500', 'bg' => 'bg-green-500', 'gradient' => 'from-green-400 to-green-600'],
                                        ['text' => 'text-yellow-500', 'bg' => 'bg-yellow-500', 'gradient' => 'from-yellow-400 to-yellow-600'],
                                        ['text' => 'text-indigo-500', 'bg' => 'bg-indigo-500', 'gradient' => 'from-indigo-400 to-indigo-600'],
                                        ['text' => 'text-red-500', 'bg' => 'bg-red-500', 'gradient' => 'from-red-400 to-red-600'],
                                        ['text' => 'text-teal-500', 'bg' => 'bg-teal-500', 'gradient' => 'from-teal-400 to-teal-600'],
                                    ];
                                @endphp
                                <div class="space-y-6">
                                    @foreach($genreDna as $genre => $data)
                                        @php
                                            $color = $colors[$loop->index % count($colors)];
                                        @endphp
                                        <div class="group">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-base font-bold text-gray-800 dark:text-gray-200 capitalize">{{ $genre }}</span>
                                                <span class="text-sm font-bold {{ $color['text'] }}">{{ $data['percent'] }}%</span>
                                            </div>
                                            <!-- The Hero Section Bars: Thicker (h-4) & Gradient -->
                                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-4 overflow-hidden shadow-inner">
                                                <div class="bg-gradient-to-r {{ $color['gradient'] }} h-4 rounded-full transition-all duration-1000 ease-out shadow-sm group-hover:scale-x-105 origin-left" style="width: {{ $data['percent'] }}%; min-width: 8%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-gray-500 italic mb-2">Not enough data to analyze musical taste yet.</p>
                                    <p class="text-sm text-gray-400">Start sharing and liking songs!</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-0 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>

            </div>
            <x-music-share-modal />
        </div>
    </div>
</x-app-layout>

