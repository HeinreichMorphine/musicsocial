<x-app-layout pageTitle="{{ $user->name }} is Following">
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
                            {{ $user->name }} is Following
                        </h2>

                        @if($following->count() > 0)
                            <div class="grid grid-cols-1 gap-4">
                                @foreach ($following as $followedUser)
                                    <x-user-list-item :user="$followedUser" />
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10 text-gray-500">
                                <p>Not following anyone yet.</p>
                            </div>
                        @endif

                        {{ $following->links() }}
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
