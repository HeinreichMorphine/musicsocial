@props(['user'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800/50 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition-all hover:shadow-md flex items-center justify-between w-full overflow-hidden']) }}
     x-data="{ 
         followed: {{ auth()->user()->following()->where('user_id', $user->id)->exists() ? 'true' : 'false' }},
         loading: false 
     }">
    
    <div class="flex items-center min-w-0 flex-1 mr-2 overflow-hidden">
        <a href="{{ route('profile.show', $user->name) }}" wire:navigate class="shrink-0">
            <x-user-avatar :user="$user" class="h-10 w-10 border-2 border-white dark:border-gray-700 shadow-sm hover:opacity-90 transition-opacity" />
        </a>
        <div class="ml-2 min-w-0 flex-1">
            <a href="{{ route('profile.show', $user->name) }}" wire:navigate class="block text-sm font-bold text-gray-900 dark:text-white truncate hover:text-custom-mid-blue dark:hover:text-blue-400 transition-colors">
                {{ $user->name }}
            </a>
            @if($user->username)
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@<span>{{ $user->username }}</span></p>
            @endif
        </div>
    </div>

    @if(auth()->id() !== $user->id)
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
                    window.location.reload();
                })
                .catch(err => console.error(err))
                .finally(() => loading = false);
            "
            :disabled="loading"
            :class="followed 
                ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 hover:border-red-200 dark:hover:border-red-800 border border-transparent' 
                : 'bg-custom-mid-blue text-white hover:bg-custom-dark-blue shadow-md hover:shadow-lg'"
            class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 disabled:opacity-50 flex items-center justify-center min-w-[80px] whitespace-nowrap"
        >
            <span x-show="!loading" x-text="followed ? 'Unfollow' : 'Follow'"></span>
            <span x-show="loading" class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
        </button>
    @endif
</div>
