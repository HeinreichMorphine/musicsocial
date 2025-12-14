<x-app-layout pageTitle="View Share">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
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

            <div class="col-span-12 md:col-span-7">
                <x-share-card :share="$share" />

                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Comments</h2>
                    
                    {{-- Comment Form --}}
                    <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                        <form action="{{ route('shares.comments.store', $share) }}" method="POST">
                            @csrf
                            <div class="flex items-start space-x-4">
                                <img src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150' }}" alt="your avatar" class="h-10 w-10 rounded-full object-cover">
                                <div class="flex-1">
                                    <textarea name="body" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-custom-blue focus:ring focus:ring-custom-blue-light focus:ring-opacity-50" placeholder="Add a comment..."></textarea>
                                    <button type="submit" class="mt-2 px-4 py-2 bg-custom-blue text-white rounded-lg hover:bg-custom-dark-blue transition-colors">Post Comment</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Existing Comments --}}
                    <div class="space-y-4">
                        @forelse ($share->comments->sortByDesc('created_at') as $comment)
                            <x-comment :comment="$comment" />
                        @empty
                            <div class="bg-white rounded-2xl shadow-sm p-4 text-center text-gray-500">
                                <p>No comments yet. Be the first to comment!</p>
                            </div>
                        @endforelse
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
    </div>
</x-app-layout>
