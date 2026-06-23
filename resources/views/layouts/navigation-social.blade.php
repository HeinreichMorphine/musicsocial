                        @php
                            $isHome = Route::is('dashboard');
                            $isProfile = Route::is('profile.show') || Route::is('profile.edit') || Route::is('profile.*');
                            $isSettings = Route::is('settings.index');
                            $isDiscovery = Route::is('discovery');
                            $baseClasses = 'flex items-center gap-3 px-4 py-3 rounded-xl transition-all group text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-zinc-800/50 font-medium';
                            $activeClasses = ' !text-indigo-600 dark:!text-white !font-semibold bg-indigo-50 dark:bg-indigo-600/10 border border-indigo-100 dark:border-indigo-500/20';
                            $svgBaseClasses = 'w-5 h-5 text-gray-400 dark:text-zinc-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors shrink-0';
                            $svgActiveClasses = ' !text-indigo-600 dark:!text-indigo-400';
                        @endphp

                        <nav class="space-y-2 flex flex-col">
                            <a href="{{ route('dashboard') }}" wire:navigate
                               class="{{ $baseClasses }} @if($isHome) {{ $activeClasses }} @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $svgBaseClasses }} @if($isHome) {{ $svgActiveClasses }} @endif">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                                <span class="truncate">Home</span>
                            </a>

                            <a href="{{ route('profile.show', auth()->user()->name) }}" wire:navigate
                               class="{{ $baseClasses }} @if($isProfile) {{ $activeClasses }} @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $svgBaseClasses }} @if($isProfile) {{ $svgActiveClasses }} @endif"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.25a.75.75 0 0 1-.22-.515v-.315a6.666 6.666 0 0 1 2.78-4.757c.307-.22.682-.338 1.066-.338h6.148c.384 0 .759.118 1.066.338a6.666 6.666 0 0 1 2.78 4.757v.315c0 .325-.29.515-.514.515z" /></svg>
                                <span class="truncate">Profile</span>
                            </a>

                            <a href="{{ route('discovery') }}" wire:navigate class="{{ $baseClasses }} @if($isDiscovery) {{ $activeClasses }} @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $svgBaseClasses }} @if($isDiscovery) {{ $svgActiveClasses }} @endif"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                <span class="truncate">Discover</span>
                            </a>

                            <a href="{{ route('playlists.index') }}" wire:navigate class="{{ $baseClasses }} @if(Route::is('playlists.*')) {{ $activeClasses }} @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $svgBaseClasses }} @if(Route::is('playlists.*')) {{ $svgActiveClasses }} @endif">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                                <span class="truncate">Playlists</span>
                            </a>

                            <a href="{{ route('settings.index') }}" wire:navigate class="{{ $baseClasses }} @if($isSettings) {{ $activeClasses }} @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $svgBaseClasses }} @if($isSettings) {{ $svgActiveClasses }} @endif">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <span class="truncate">Settings</span>
                            </a>
                        </nav>
