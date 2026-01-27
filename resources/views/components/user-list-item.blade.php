@props(['user'])

<div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-white/50 p-4 transition-all hover:shadow-md flex items-center justify-between"
     x-data="{ 
         followed: {{ auth()->user()->following()->where('user_id', $user->id)->exists() ? 'true' : 'false' }},
         loading: false 
     }">
    
    <div class="flex items-center min-w-0 flex-1 mr-4">
        <a href="{{ route('profile.show', $user->name) }}" class="shrink-0">
            <x-user-avatar :user="$user" class="h-12 w-12 border-2 border-white shadow-sm hover:opacity-90 transition-opacity" />
        </a>
        <div class="ml-3 min-w-0">
            <a href="{{ route('profile.show', $user->name) }}" class="block text-base font-bold text-gray-900 truncate hover:text-custom-mid-blue transition-colors">
                {{ $user->name }}
            </a>
            @if($user->username)
                <p class="text-sm text-gray-500 truncate">@<span>{{ $user->username }}</span></p>
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
                    window.reloadWithScroll();
                })
                .catch(err => console.error(err))
                .finally(() => loading = false);
            "
            :disabled="loading"
            :class="followed 
                ? 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-red-600 hover:border-red-200 border border-transparent' 
                : 'bg-custom-mid-blue text-white hover:bg-custom-dark-blue shadow-md hover:shadow-lg'"
            class="shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-200 disabled:opacity-50 flex items-center justify-center min-w-[90px]"
        >
            <span x-show="!loading" x-text="followed ? 'Unfollow' : 'Follow'"></span>
            <span x-show="loading" class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
        </button>
    @endif
</div>
