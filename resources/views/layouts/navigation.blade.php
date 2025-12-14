<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

// Determine the avatar URL once
$avatarUrl = Auth::user()->profile_picture
    ? Storage::url(Auth::user()->profile_picture)
    : 'https://via.placeholder.com/40';

// Retrieve the dynamic page title that was explicitly shared from app.blade.php
$pageTitle = View::shared('pageTitle', __('Dashboard'));

?>

<nav x-data="{ open: false }" class="bg-[#ebf8ff] border-b border-blue-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('icons/reso.png') }}" alt="Platform Logo" class="block h-20 w-auto">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-4 sm:flex items-center">
                    <span class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $pageTitle }}
                    </span>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <form action="{{ route('user.search') }}" method="GET" class="flex items-center">
                    <input type="text" name="user" placeholder="Search for users..." class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                    <button type="submit" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md">Search</button>
                </form>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <!-- Notification Bell -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Badge -->
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
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
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <img src="{{ $avatarUrl }}" alt="{{ Auth::user()->name }}'s Avatar" class="h-8 w-8 rounded-full object-cover me-2">

                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>




                    <x-slot name="content">
                        <x-dropdown-link :href="route('settings.index')">
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

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex items-center space-x-3">
                <img src="{{ $avatarUrl }}" alt="{{ Auth::user()->name }}'s Avatar" class="h-10 w-10 rounded-full object-cover">

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('settings.index')">
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
