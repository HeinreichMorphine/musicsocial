<div x-data="{ showSpotifyBetaModal: false }"
     @open-spotify-beta-modal.window="showSpotifyBetaModal = true"
     @keydown.escape.window="showSpotifyBetaModal = false"
     class="relative z-[100]"
     style="display: none;"
     x-show="showSpotifyBetaModal"
     x-cloak>
     
    <div x-show="showSpotifyBetaModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showSpotifyBetaModal"
                 @click.away="showSpotifyBetaModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                
                <div class="px-6 pb-6 pt-8 sm:p-8">
                    <!-- Header -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-[#1DB954]/10 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-8 h-8 text-[#1DB954]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-[#1DB954] uppercase tracking-wider mb-1">Closed Beta</div>
                            <h3 class="text-xl font-bold text-gray-900 leading-tight">Spotify Login Restricted</h3>
                        </div>
                    </div>

                    <!-- Intro Text -->
                    <p class="text-[15px] text-gray-600 leading-relaxed mb-6">
                        Due to Spotify's new <strong>B2B API policy</strong>, our Spotify integration is in a closed development mode. Only manually approved accounts can sign in via Spotify.
                    </p>

                    <div class="space-y-3 mb-6">
                        <!-- Warning Box -->
                        <div class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 bg-white">
                            <div class="shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div class="text-sm text-gray-600 leading-relaxed">
                                <strong class="text-gray-900">Not on the allowlist?</strong> Your Spotify login will be rejected with a 403 error. Please use Google or Email instead.
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="flex items-start gap-3 p-4 rounded-2xl border border-blue-100 bg-blue-50/50">
                            <div class="shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="text-sm text-gray-600 leading-relaxed">
                                <strong class="text-gray-900">Want access?</strong> Email <a href="mailto:adamakib507@gmail.com" class="text-blue-600 font-medium hover:underline">adamakib507@gmail.com</a> to request whitelist approval.
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        <a href="{{ route('social.redirect', 'google') }}" class="w-full flex items-center justify-center gap-3 bg-[#0F172A] hover:bg-black text-white px-6 py-3.5 rounded-xl text-[15px] font-bold transition-all shadow-lg shadow-gray-900/10">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                            Continue with Google instead
                        </a>
                        
                        <button type="button" @click="showSpotifyBetaModal = false" class="w-full flex items-center justify-center px-6 py-3 text-[15px] font-bold text-gray-500 hover:text-gray-800 transition-colors">
                            Go back
                        </button>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                        <a href="{{ route('social.redirect', 'spotify') }}" class="text-[13px] text-gray-400 hover:text-gray-600 underline decoration-gray-300 underline-offset-4 transition-colors font-medium">
                            Whitelisted beta tester? Proceed anyway &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
