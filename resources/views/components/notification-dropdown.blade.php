<x-dropdown align="right" width="64">
    <x-slot name="trigger">
        <button class="relative inline-flex items-center p-2 text-gray-400 hover:text-gray-500 focus:outline-none transition duration-150 ease-in-out">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Badge -->
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
                    {{ auth()->user()->unreadNotifications->count() }}
                </span>
            @endif
        </button>
    </x-slot>

    <x-slot name="content">
        @if(auth()->user()->unreadNotifications->isEmpty())
            <div class="px-4 py-2 text-xs text-gray-500">No new notifications</div>
        @else
            @foreach(auth()->user()->unreadNotifications as $notification)
                @php
                    $shareId = $notification->data['share_id'] ?? null;
                    $commentId = $notification->data['comment_id'] ?? null;
                    $playlistId = $notification->data['playlist_id'] ?? null;
                    $followerName = $notification->data['follower_name'] ?? null;
                    
                    if ($shareId) {
                        $notificationUrl = route('shares.show', $shareId) . ($commentId ? '#comment-' . $commentId : '');
                    } elseif ($playlistId) {
                        $notificationUrl = route('playlists.index');
                    } elseif ($followerName) {
                        $notificationUrl = route('profile.show', $followerName);
                    } else {
                        $notificationUrl = route('dashboard');
                    }
                @endphp
                <a 
                    href="{{ $notificationUrl }}" 
                    wire:navigate
                    onclick="fetch('{{ route('notifications.markAsRead', $notification->id) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })"
                    class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                    <div class="text-sm font-medium">
                        {{ $notification->data['message'] ?? 'New Notification' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                </a>
            @endforeach
            <div class="border-t border-gray-100 dark:border-gray-800"></div>
            <form method="POST" action="{{ route('notifications.markRead') }}">
                @csrf
                <button type="submit" class="block w-full px-4 py-3 text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition duration-150 ease-in-out">
                    Mark all as read
                </button>
            </form>
        @endif
    </x-slot>
</x-dropdown>
