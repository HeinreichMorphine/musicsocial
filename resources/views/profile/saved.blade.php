<x-app-layout>
    <div class="py-12 bg-gray-50 bg-opacity-50 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('profile.partials.header', ['user' => $user])

            <div class="mt-6 sm:flex sm:space-x-8">
                <!-- Main Content (Feed) -->
                <div class="sm:w-2/3">
                    @forelse ($shares as $share)
                        <x-share-card :share="$share" />
                    @empty
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                            <!-- Ribbon Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-4 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0111.186 0z" />
                            </svg>
                            <p class="text-lg">You haven't saved any posts yet.</p>
                            <a href="{{ route('dashboard') }}" class="text-custom-mid-blue hover:underline mt-2 inline-block">Explore the feed</a>
                        </div>
                    @endforelse

                    <div class="mt-6">
                        {{ $shares->links() }}
                    </div>
                </div>

                <!-- Right Sidebar using Component -->
                <x-sidebar-right />
            </div>
        </div>
    </div>
</x-app-layout>
