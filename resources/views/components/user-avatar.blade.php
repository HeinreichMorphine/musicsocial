@props(['user'])

@php
    $displayName = $user->name ?? $user->username ?? 'User';
    $initial = strtoupper(substr($displayName, 0, 1));
    // Generate a consistent color based on user name
    $colors = ['bg-indigo-600', 'bg-blue-600', 'bg-purple-600', 'bg-pink-600', 'bg-rose-600', 'bg-amber-600', 'bg-emerald-600', 'bg-teal-600'];
    $colorIndex = ord($displayName[0] ?? 'A') % count($colors);
    $bgColor = $colors[$colorIndex];
@endphp

<div x-data="{ avatarError: false }" {{ $attributes->merge(['class' => 'relative inline-block overflow-hidden rounded-full shrink-0']) }}>
    @if($user->profile_picture && $user->profile_picture !== 'profile_pictures/default_black_box.png')
        <img src="{{ Storage::url($user->profile_picture) }}"
             alt="{{ $user->name }}"
             class="w-full h-full object-cover"
             x-show="!avatarError"
             x-on:error="avatarError = true">
             
        <div class="w-full h-full {{ $bgColor }} flex items-center justify-center text-white font-bold uppercase"
             x-show="avatarError"
             style="display: none;">
            {{ $initial }}
        </div>
    @elseif($user->avatar)
        <img src="{{ $user->avatar }}"
             alt="{{ $user->name }}"
             class="w-full h-full object-cover"
             x-show="!avatarError"
             x-on:error="avatarError = true">

        <div class="w-full h-full {{ $bgColor }} flex items-center justify-center text-white font-bold uppercase"
             x-show="avatarError"
             style="display: none;">
            {{ $initial }}
        </div>
    @else
        <div class="w-full h-full {{ $bgColor }} flex items-center justify-center text-white font-bold uppercase shadow-inner">
            {{ $initial }}
        </div>
    @endif
</div>
