                        @php
                            $isHome = Route::is('dashboard');
                            $isProfile = Route::is('profile.show') || Route::is('profile.edit') || Route::is('profile.*');
                            $isSettings = Route::is('settings.index');
                            $isDiscovery = Route::is('discovery');
                            $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                            $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                        @endphp

                        <nav x-data="{ profileMenuOpen: @js($isProfile) }" class="space-y-2">

                            <a href="{{ route('dashboard') }}"
                               class="{{ $baseClasses }} @if($isHome) {{ $activeClasses }} @endif">
                                <img src="{{ asset('icons/home.png') }}" alt="Home" class="w-6 h-6 mr-4">
                                Home
                            </a>

                            <div>
                                <button x-on:click="profileMenuOpen = !profileMenuOpen"
                                   class="{{ $baseClasses }} w-full justify-start text-gray-800 @if($isProfile) {{ $activeClasses }} @endif">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 mr-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.25a.75.75 0 0 1-.22-.515v-.315a6.666 6.666 0 0 1 2.78-4.757c.307-.22.682-.338 1.066-.338h6.148c.384 0 .759.118 1.066.338a6.666 6.666 0 0 1 2.78 4.757v.315c0 .325-.29.515-.514.515z" /></svg>
                                    Profile

                                    <svg class="ml-auto w-4 h-4 transform transition duration-200 text-gray-500"
                                         :class="{ 'rotate-180': profileMenuOpen }"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="profileMenuOpen" x-transition class="py-1 space-y-1">

                                    <a href="{{ route('profile.show', auth()->user()->name) }}"
                                       class="flex items-center p-2 rounded-full font-semibold text-sm hover:bg-gray-200 transition duration-150 @if(Route::is('profile.show')) {{ $activeClasses }} @endif">
                                        <span class="ml-8">View Public Profile</span>
                                    </a>

                                    <a href="{{ route('profile.edit') }}"
                                       class="flex items-center p-2 rounded-full font-semibold text-sm hover:bg-gray-200 transition duration-150 @if(Route::is('profile.edit')) {{ $activeClasses }} @endif">
                                        <span class="ml-8">Edit Settings</span>
                                    </a>

                                </div>
                            </div>
                            <a href="{{ route('discovery') }}" class="{{ $baseClasses }} @if($isDiscovery) {{ $activeClasses }} @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 mr-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                Discover
                            </a>

                            <!-- Settings Link -->
                            <a href="{{ route('settings.index') }}"
                               class="{{ $baseClasses }} @if($isSettings) {{ $activeClasses }} @endif">
                                <img src="{{ asset('icons/setting.png') }}" alt="Settings" class="w-6 h-6 mr-4">
                                Settings
                            </a>
                        </nav>
