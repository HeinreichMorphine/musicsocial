<div x-data="{ show: false }"
     x-on:open-spotify-link-modal.window="show = true"
     x-on:keydown.escape.window="show = false"
     class="relative z-500"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     style="display: none;"
     x-show="show">

    <!-- Backdrop -->
    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500/75 dark:bg-black/80 backdrop-blur-sm transition-opacity"
         aria-hidden="true"
         @click="show = false"></div>

    <!-- Modal Panel -->
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#121212] border border-gray-200 dark:border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                
                <div class="px-6 py-8 sm:p-8">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-12 sm:w-12 mb-4 sm:mb-0">
                            <!-- Spotify Icon -->
                            <svg class="h-8 w-8 drop-shadow-md" fill="#1DB954" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-5 sm:mt-0 sm:text-left">
                            <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-white" id="modal-title">Connect Spotify</h3>
                            <div class="mt-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    To add songs directly to your Spotify playlists, you need to link your Spotify account in Settings.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-black/20 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100 dark:border-white/5">
                    <a href="{{ route('settings.index') }}"
                       class="inline-flex w-full justify-center rounded-full bg-green-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-green-400 sm:ml-3 sm:w-auto transition-all hover:scale-105 ring-1 ring-inset ring-green-600/20">
                        Go to Settings
                    </a>
                    <button type="button" 
                            class="mt-3 inline-flex w-full justify-center rounded-full bg-white dark:bg-white/5 px-5 py-2.5 text-sm font-bold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/10 sm:mt-0 sm:w-auto transition-all"
                            @click="show = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
