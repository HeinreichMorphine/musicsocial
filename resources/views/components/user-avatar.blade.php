@props(['user', 'class' => 'w-10 h-10'])

<div x-data="{ avatarError: false }" {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    @if($user->profile_picture)
        <img src="{{ Storage::url($user->profile_picture) }}"
             alt="{{ $user->name }}"
             {{ $attributes->merge(['class' => $class . ' rounded-full object-cover']) }}
             x-show="!avatarError"
             x-on:error="avatarError = true">
             
        <div {{ $attributes->merge(['class' => $class . ' rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0']) }}
             x-show="avatarError"
             style="display: none;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    @else
        <div {{ $attributes->merge(['class' => $class . ' rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0']) }}>
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    @endif
</div>
