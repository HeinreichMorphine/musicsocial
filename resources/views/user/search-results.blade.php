<x-app-layout pageTitle="Search Results for '{{ $searchQuery }}'">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                    <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                        @php
                            $isHome = Route::is('dashboard');
                            $isProfile = false;
                            $isSettings = false;
                            $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                            $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                        @endphp
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <div class="col-span-12 md:col-span-7">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            Search Results for "{{ $searchQuery }}"
                        </h2>

                        <div x-data="{ activeTab: '{{ request('tab', 'users') }}' }" class="mb-6">
                            <!-- Tabs Navigation -->
                            <div class="border-b border-gray-200 mb-6">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    <button @click="activeTab = 'users'"
                                       :class="activeTab === 'users' 
                                            ? 'border-custom-mid-blue text-custom-mid-blue' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                       class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                                       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                                          <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                                        </svg>
                                        Users
                                    </button>

                                    <button @click="activeTab = 'posts'"
                                       :class="activeTab === 'posts' 
                                            ? 'border-custom-mid-blue text-custom-mid-blue' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                       class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                                          <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd" />
                                        </svg>
                                        Posts
                                    </button>
                                </nav>
                            </div>

                            <!-- Users Tab Content -->
                            <div x-show="activeTab === 'users'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                @if ($users->isNotEmpty())
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach ($users as $userItem)
                                            <x-user-list-item :user="$userItem" />
                                        @endforeach
                                    </div>
                                    <div class="mt-4">
                                         {{ $users->appends(['query' => $searchQuery, 'tab' => 'users'])->links() }}
                                    </div>
                                @else
                                    <div class="text-center py-10 text-gray-500">
                                        <p>No users found matching "{{ $searchQuery }}".</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Posts Tab Content -->
                             <div x-show="activeTab === 'posts'" x-cloak x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                @if ($shares->isNotEmpty())
                                    <div class="space-y-6">
                                        @foreach ($shares as $share)
                                            <x-share-card :share="$share" />
                                        @endforeach
                                    </div>
                                    <div class="mt-4">
                                         {{ $shares->appends(['query' => $searchQuery, 'tab' => 'posts'])->links() }}
                                    </div>
                                @else
                                    <div class="text-center py-10 text-gray-500">
                                        <p>No posts found matching "{{ $searchQuery }}".</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                    <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
