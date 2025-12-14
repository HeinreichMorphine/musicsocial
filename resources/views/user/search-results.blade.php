<x-app-layout pageTitle="Search Results for '{{ $searchQuery }}'">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
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

            <div class="col-span-12 md:col-span-7">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            Search Results for "{{ $searchQuery }}"
                        </h2>

                        @forelse ($users as $userItem)
                            <div class="mb-4 p-4 border rounded-lg flex items-center">
                                <img src="{{ $userItem->profile_picture ? Storage::url($userItem->profile_picture) : 'https://via.placeholder.com/40' }}" alt="{{ $userItem->name }}'s Avatar" class="h-10 w-10 rounded-full object-cover mr-4">
                                <div>
                                    <a href="{{ route('user.profile', $userItem->name) }}" class="font-bold text-lg text-blue-600 hover:underline">{{ $userItem->name }}</a>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-600">No users found matching "{{ $searchQuery }}".</p>
                        @endforelse

                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    @include('layouts.sidebar-right')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
