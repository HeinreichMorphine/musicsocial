<x-app-layout pageTitle="View Share">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-0 pt-4">
                    <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 transition-colors mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Back to Feed</span>
                        </a>
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                <x-share-card 
                    :share="$share" 
                    :paginatedComments="$comments"
                    :totalCount="$totalCommentsCount"
                    :previewComments="$previewComments"
                />



            </div>

            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-0 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
