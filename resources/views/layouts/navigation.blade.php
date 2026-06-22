<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;



// Retrieve the dynamic page title that was explicitly shared from app.blade.php
$pageTitle = View::shared('pageTitle', __('Dashboard'));

?>

<nav x-data="{ 
    open: false,
    darkMode: localStorage.getItem('darkMode') === 'true',
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" class="sticky top-0 z-50 bg-white/80 dark:bg-black backdrop-blur-md border-b border-indigo-100 dark:border-white/10 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Mobile Dark Mode Toggle was moved to right side of nav -->

                <div class="shrink-0 flex items-center pl-1 sm:pl-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center" wire:navigate>
                        <img src="{{ asset('icons/reso.png') }}" alt="Platform Logo" class="block h-12 w-auto">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-4 sm:flex items-center">
                    <span class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $pageTitle }}
                    </span>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-6">
                <form action="{{ route('user.search') }}" method="GET" class="flex items-center" x-data="{ expanded: false }">
                    <div class="relative flex items-center">
                        <button type="button" @click="expanded = true; $nextTick(() => $refs.searchInput.focus())" x-show="!expanded" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-gray-800" title="Global Search">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        
                        <div x-show="expanded" x-transition.opacity.duration.300ms class="relative" @click.away="expanded = false" style="display: none;">
                             <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input x-ref="searchInput" type="text" name="query" placeholder="Search..." class="pl-10 pr-4 py-2 border-gray-300 dark:border-white/10 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-300 dark:focus:border-indigo-500 focus:ring focus:ring-indigo-200 dark:focus:ring-indigo-800 focus:ring-opacity-50 rounded-full shadow-sm w-64 text-sm transition-all">
                        </div>
                    </div>
                </form>

                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <div class="mr-2">
                        <button @click="toggleDarkMode()" class="p-2 text-gray-500 hover:text-gray-700 bg-gray-50 dark:bg-white/5 rounded-full dark:text-gray-400 dark:hover:text-white focus:outline-none transition-colors duration-200" title="Toggle Dark Mode">
                            <!-- Sun Icon (show when dark) -->
                            <svg x-show="darkMode" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <!-- Moon Icon (show when light) -->
                            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>
                    </div>
                    <!-- Notification Bell -->
                    <x-dropdown align="right" width="48">
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
                                    $notificationUrl = $shareId
                                        ? route('shares.show', $shareId) . ($commentId ? '#comment-' . $commentId : '')
                                        : route('dashboard');
                                @endphp
                                <a 
                                    href="{{ $notificationUrl }}" 
                                    wire:navigate
                                    onclick="fetch('{{ route('notifications.markAsRead', $notification->id) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    <div class="text-sm">
                                        {{ $notification->data['message'] ?? 'New Notification' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                                </a>
                            @endforeach
                             <div class="border-t border-gray-100"></div>
                             <form method="POST" action="{{ route('notifications.markRead') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                    Mark all as read
                                </button>
                             </form>
                        @endif
                    </x-slot>
                </x-dropdown>

                <!-- User Dropdown (Existing) -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-200 bg-white dark:bg-black hover:text-gray-700 dark:hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <x-user-avatar :user="Auth::user()" class="h-8 w-8 me-2" />

                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>




                    <x-slot name="content">
                        <x-dropdown-link :href="route('settings.index')" wire:navigate>
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>

        <div class="flex items-center justify-end flex-1 sm:hidden space-x-3 pr-1">
                <!-- Existing Mobile Hamburger Button (Only used for settings dropdown when using bottom nav) -->
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white dark:bg-black border-t border-indigo-100 dark:border-white/10">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-800">
            <div class="px-4 flex items-center space-x-3">
                <x-user-avatar :user="Auth::user()" class="h-10 w-10" />

                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('settings.index')" wire:navigate>
                    {{ __('Settings') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
