<x-app-layout pageTitle="{{ $user->name }}'s Taste DNA">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <!-- Left Sidebar -->
                <div class="hidden md:block col-span-2">
                    <div class="sticky top-0 pt-4">
                        <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-span-12 md:col-span-7">
                    
                    <!-- Profile Header (Shared) -->
                    @include('profile.partials.header', ['user' => $user, 'badge' => $badge ?? null])

                    <!-- Taste DNA Content -->
                    <div class="space-y-6">
                        
                        <!-- Genre DNA Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-gray-100">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path fill-rule="evenodd" d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 .437-.32l3.869-7.525A1.5 1.5 0 0 0 19.467.43a.75.75 0 0 1 .485 1.22zM8.36 1.765a.75.75 0 0 1 1.498 0v16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 .437-.32l.758-1.22V3.75c0-.66.33-1.27.876-1.63L8.36 1.765z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                Genre DNA
                            </h3>
                            
                            @if(count($genreDna) > 0)
                                @php
                                    $colors = [
                                        ['text' => 'text-purple-500', 'bg' => 'bg-purple-500', 'gradient' => 'from-purple-400 to-purple-600'],
                                        ['text' => 'text-blue-500', 'bg' => 'bg-blue-500', 'gradient' => 'from-blue-400 to-blue-600'],
                                        ['text' => 'text-pink-500', 'bg' => 'bg-pink-500', 'gradient' => 'from-pink-400 to-pink-600'],
                                        ['text' => 'text-green-500', 'bg' => 'bg-green-500', 'gradient' => 'from-green-400 to-green-600'],
                                        ['text' => 'text-yellow-500', 'bg' => 'bg-yellow-500', 'gradient' => 'from-yellow-400 to-yellow-600'],
                                        ['text' => 'text-indigo-500', 'bg' => 'bg-indigo-500', 'gradient' => 'from-indigo-400 to-indigo-600'],
                                        ['text' => 'text-red-500', 'bg' => 'bg-red-500', 'gradient' => 'from-red-400 to-red-600'],
                                        ['text' => 'text-teal-500', 'bg' => 'bg-teal-500', 'gradient' => 'from-teal-400 to-teal-600'],
                                    ];
                                @endphp
                                <div class="space-y-6">
                                    @foreach($genreDna as $genre => $data)
                                        @php
                                            $color = $colors[$loop->index % count($colors)];
                                        @endphp
                                        <div class="group">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-base font-bold text-gray-800 capitalize">{{ $genre }}</span>
                                                <span class="text-sm font-bold {{ $color['text'] }}">{{ $data['percent'] }}%</span>
                                            </div>
                                            <!-- The Hero Section Bars: Thicker (h-4) & Gradient -->
                                            <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden shadow-inner">
                                                <div class="bg-gradient-to-r {{ $color['gradient'] }} h-4 rounded-full transition-all duration-1000 ease-out shadow-sm group-hover:scale-x-105 origin-left" style="width: {{ $data['percent'] }}%; min-width: 8%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-gray-500 italic mb-2">Not enough data to analyze musical taste yet.</p>
                                    <p class="text-sm text-gray-400">Start sharing and liking songs!</p>
                                </div>
                            @endif
                        </div>

                        <!-- Taste Radar Chart -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-gray-100">
                             <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-purple-100 text-purple-600 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <!-- Outer Ring -->
                                        <circle cx="12" cy="12" r="10" />
                                        <!-- Inner Ring -->
                                        <circle cx="12" cy="12" r="6" />
                                        <!-- Keepout Zone -->
                                        <circle cx="12" cy="12" r="2" />
                                        <!-- Crosshairs -->
                                        <line x1="12" y1="2" x2="12" y2="22" />
                                        <line x1="2" y1="12" x2="22" y2="12" />
                                        <!-- Sweep Sector (Bottom Left) -->
                                        <path d="M12 12 L5 19 A 10 10 0 0 0 12 22 Z" fill="currentColor" stroke="none" opacity="0.6" />
                                        <!-- Blips -->
                                        <circle cx="17" cy="7" r="1.5" fill="currentColor" stroke="none" />
                                        <circle cx="6" cy="9" r="1.5" fill="currentColor" stroke="none" />
                                        <circle cx="15" cy="16" r="1.5" fill="currentColor" stroke="none" />
                                    </svg>
                                </span>
                                Taste Radar
                            </h3>
                            
                            @if(count($genreLabels) > 0)
                                <div class="relative h-80 w-full flex justify-center">
                                    <canvas id="tasteRadar"></canvas>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-gray-500 italic">Not enough data for Radar Chart.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Chart.js Script -->
                        @if(count($genreLabels) > 0)
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const ctx = document.getElementById('tasteRadar').getContext('2d');
                                    
                                    // Gradient Fill
                                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                                    gradient.addColorStop(0, 'rgba(124, 58, 237, 0.5)'); // Purple
                                    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.5)'); // Blue

                                    new Chart(ctx, {
                                        type: 'radar',
                                        data: {
                                            labels: @json($genreLabels),
                                            datasets: [{
                                                label: 'Taste Intensity',
                                                data: @json($genreValues),
                                                backgroundColor: gradient,
                                                borderColor: 'rgba(99, 102, 241, 1)', // Indigo-500
                                                borderWidth: 2,
                                                pointBackgroundColor: 'rgba(255, 255, 255, 1)',
                                                pointBorderColor: '#fff',
                                                pointHoverBackgroundColor: '#fff',
                                                pointHoverBorderColor: 'rgba(99, 102, 241, 1)'
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            scales: {
                                                r: {
                                                    min: 0,
                                                    max: 100,
                                                    angleLines: {
                                                        display: true,
                                                        color: 'rgba(0, 0, 0, 0.1)'
                                                    },
                                                    grid: {
                                                        color: 'rgba(0, 0, 0, 0.05)'
                                                    },
                                                    pointLabels: {
                                                        font: {
                                                            size: 14,
                                                            family: "'Figtree', sans-serif",
                                                            weight: 'bold'
                                                        },
                                                        color: '#374151' // Gray-700
                                                    },
                                                    ticks: {
                                                        display: false, // Hide numeric ticks
                                                        backdropColor: 'transparent'
                                                    }
                                                }
                                            },
                                            plugins: {
                                                legend: {
                                                    display: false
                                                },
                                                tooltip: {
                                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                                    padding: 12,
                                                    titleFont: { family: "'Figtree', sans-serif" },
                                                    bodyFont: { family: "'Figtree', sans-serif" }
                                                }
                                            },
                                            elements: {
                                                line: {
                                                    tension: 0.3 // Smooth curves
                                                }
                                            }
                                        }
                                    });
                                });
                            </script>
                        @endif

                        <!-- Taste Twins Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-gray-100">
                             <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-pink-100 text-pink-600 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path d="M11.645 20.91a.75.75 0 0 1-1.29 0C8.343 16.63 3.75 12.55 3.75 8.25 3.75 5.399 5.399 3.75 8.25 3.75c1.74 0 3.333.92 4.25 2.336C13.417 4.67 15.01 3.75 16.75 3.75c2.851 0 4.5 1.649 4.5 4.5 0 4.3-4.593 8.38-6.605 10.369a.75.75 0 0 1-1.29-.012Z" />
                                    </svg>
                                </span>
                                Taste Twins
                            </h3>
                            
                            @if($tasteTwins->isNotEmpty())
                                <div class="grid grid-cols-1 gap-4">
                                    @foreach($tasteTwins as $twin)
                                        <div class="flex items-center p-4 rounded-2xl border border-gray-100 hover:shadow-lg transition-all duration-300 bg-white group">
                                            <div class="relative">
                                                <img src="{{ $twin->profile_picture_url }}" alt="{{ $twin->name }}" class="h-16 w-16 rounded-full object-cover border-2 border-white shadow-md">
                                                <span class="absolute -bottom-1 -right-1 bg-green-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-white">
                                                    {{ $twin->match_score }}%
                                                </span>
                                            </div>
                                            
                                            <div class="ml-4 flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <a href="{{ route('profile.show', $twin->name) }}" class="text-lg font-bold text-gray-900 truncate hover:text-custom-mid-blue transition-colors">
                                                        {{ $twin->name }}
                                                    </a>
                                                    @if(auth()->id() !== $twin->id)
                                                        <form action="{{ route('users.follow', $twin) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="text-xs font-bold px-3 py-1 rounded-full {{ auth()->user()->isFollowing($twin) ? 'bg-gray-100 text-gray-500' : 'bg-custom-mid-blue text-white hover:bg-custom-dark-blue' }} transition-colors">
                                                               {{ auth()->user()->isFollowing($twin) ? 'Following' : 'Follow' }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                                
                                                <div class="mt-1">
                                                     <p class="text-sm font-semibold text-green-600 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-1">
                                                          <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ $twin->match_score }}% Match
                                                     </p>
                                                     <p class="text-xs text-gray-500 mt-0.5 truncate">
                                                        {{ $twin->common_ground }}
                                                     </p>
                                                </div>
                                            </div>
                                            
                                            <div class="ml-4 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:block">
                                                 <a href="{{ route('profile.show', $twin->name) }}" class="text-custom-mid-blue hover:underline text-sm font-medium">View Profile &rarr;</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 text-center bg-gray-50 rounded-2xl">
                                    <p class="text-gray-500 italic mb-2">No Taste Twins founds yet.</p>
                                    <p class="text-sm text-gray-400">Share more diverse music to find your tribe!</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="hidden md:block col-span-3">
                    <div class="sticky top-0 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>

            </div>
            <x-music-share-modal />
        </div>
    </div>
</x-app-layout>
