<div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-3xl mb-6 relative group transition-colors duration-300 border border-gray-100 dark:border-white/10"
     x-data="{
         followed: {{ auth()->check() && auth()->user()->isFollowing($user) ? 'true' : 'false' }},
         followersCount: {{ $user->followers()->count() }},
         loading: false
     }">
    
    <!-- Cover Image -->
    @if($user->cover_photo_url)
        <div class="w-full aspect-[3/1] bg-cover bg-center" style="background-image: url('{{ $user->cover_photo_url }}');"></div>
    @else
        <div class="w-full aspect-[3/1] bg-gradient-to-r from-blue-400 to-purple-500 object-cover"></div>
    @endif

    <div class="px-6 pb-6 text-gray-900 dark:text-gray-100 relative">
        <div class="flex flex-col sm:flex-row items-start sm:items-end -mt-12 mb-4">
             <!-- Avatar -->
             <x-user-avatar :user="$user" class="h-24 w-24 border-4 border-white dark:border-gray-800 shadow-lg" />
             
             <!-- Name & Badge -->
             <div class="mt-4 sm:mt-0 sm:ml-4 flex-1">
                  <div class="flex items-center">
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight mr-2">
                        {{ $user->name }}
                    </h2>
                    @if(isset($badge))
                        <span class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:text-yellow-400">
                          {{ $badge }}
                        </span>
                    @endif
                  </div>
             </div>

             <!-- Follow Button -->
             @if(auth()->check() && auth()->id() !== $user->id)
                <div class="mt-4 sm:mt-0">
                    <button 
                        @click="
                            loading = true;
                            fetch('{{ route('users.follow', $user) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({})
                            })
                            .then(resp => resp.json())
                            .then(data => {
                                followed = data.followed;
                                followersCount = data.followersCount;
                            })
                            .catch(err => console.error(err))
                            .finally(() => loading = false);
                        "
                        :disabled="loading"
                        :class="allowed = followed ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' : 'bg-custom-mid-blue text-white hover:bg-custom-dark-blue'"
                        class="px-6 py-2 rounded-full font-bold text-sm shadow-sm transition-all transform hover:scale-105"
                    >
                        <span x-show="!loading" x-text="followed ? 'Unfollow' : 'Follow'"></span>
                        <span x-show="loading" class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block"></span>
                    </button>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-6 mb-6 ml-1 sm:ml-28">
            <a href="{{ route('profile.followers', $user) }}" class="text-gray-600 dark:text-gray-400 hover:text-custom-mid-blue dark:hover:text-blue-400 transition-colors">
                <span class="font-bold text-gray-900 dark:text-white" x-text="followersCount">{{ $user->followers()->count() }}</span> Followers
            </a>
            <a href="{{ route('profile.following', $user) }}" class="text-gray-600 dark:text-gray-400 hover:text-custom-mid-blue dark:hover:text-blue-400 transition-colors">
                <span class="font-bold text-gray-900 dark:text-white">{{ $user->following()->count() }}</span> Following
            </a>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-100 dark:border-gray-700 mt-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('profile.show', $user->name) }}"
                   class="{{ Route::currentRouteName() === 'profile.show' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                      <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd" />
                    </svg>
                    Posts
                </a>

                <a href="{{ route('profile.taste', $user->name) }}"
                   class="{{ Route::currentRouteName() === 'profile.taste' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                      <path d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.96l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.96 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.96l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.683a1 1 0 0 1 .633.633l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.184-.551Z" />
                    </svg>
                    Taste DNA
                </a>

                <a href="{{ route('profile.shelf', $user->name) }}"
                   class="{{ Route::currentRouteName() === 'profile.shelf' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                        <path d="M10 2a1 1 0 0 1 1 1v1h1a1 1 0 1 1 0 2h-1v1h1a1 1 0 1 1 0 2h-1v1h1a1 1 0 1 1 0 2h-1v1a1 1 0 1 1-2 0v-1H8a1 1 0 1 1 0-2h1v-1H8a1 1 0 1 1 0-2h1V6H8a1 1 0 1 1 0-2h1V3a1 1 0 0 1 1-1Z" />
                    </svg>
                    Song Shelf
                </a>

                @if(auth()->check() && auth()->id() === $user->id)
                <a href="{{ route('playlists.index') }}"
                   class="{{ Route::currentRouteName() === 'playlists.index' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                        <path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    Playlists
                </a>

                <a href="{{ route('profile.saved', $user->name) }}"
                   class="{{ Route::currentRouteName() === 'profile.saved' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 mr-2">
                        <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.583A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z" clip-rule="evenodd" />
                    </svg>
                    Saved
                </a>
                @endif
            </nav>
        </div>
    </div>
</div>
