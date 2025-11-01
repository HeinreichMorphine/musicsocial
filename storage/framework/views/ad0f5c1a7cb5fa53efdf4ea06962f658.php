<!-- resources/views/layouts/navigation-social.blade.php -->
<nav class="space-y-2">
    <?php
        // Helper function to determine if a route is currently active
        $isHome = Route::is('dashboard');

        $isSettings = Route::is('settings.index');

        // Base classes for all links
        $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150';

        // Active classes
        $activeClasses = ' !text-custom-dark-blue dark:!text-dark-custom-dark-blue !font-bold bg-custom-periwinkle/50 dark:bg-dark-custom-periwinkle/50';
    ?>

    <!-- Home Link -->
    <a href="<?php echo e(route('dashboard')); ?>"
       class="<?php echo e($baseClasses); ?> <?php if($isHome): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
        <img src="<?php echo e(asset('icons/home.png')); ?>" alt="Home" class="w-6 h-6 mr-4">
        Home
    </a>

    <!-- ---------------------------------------------------------------- -->
    <!-- COLLAPSIBLE PROFILE MENU -->
    <!-- ---------------------------------------------------------------- -->

    <!-- Alpine state: Opens if we are on any profile-related page -->
    <div x-data="{ profileMenuOpen: <?php echo \Illuminate\Support\Js::from($isProfile)->toHtml() ?> }">

        <!-- Toggle Button (Main "Profile" Link) -->
        <button x-on:click="profileMenuOpen = !profileMenuOpen"
           class="<?php echo e($baseClasses); ?> w-full justify-start text-gray-800 dark:text-gray-200 <?php if($isProfile): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
            <img src="<?php echo e(asset('icons/profile.png')); ?>" alt="Profile" class="w-6 h-6 mr-4">
            Profile

            <!-- Caret Icon -->
            <svg class="ml-auto w-4 h-4 transform transition duration-200 text-gray-500 dark:text-gray-400"
                 :class="{ 'rotate-180': profileMenuOpen }"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Collapsible Sub-menu -->
        <div x-show="profileMenuOpen" x-transition class="py-1 space-y-1">

            <!-- Sub-link 1: View Public Profile -->
            <a href="<?php echo e(route('profile.show', auth()->user()->name)); ?>"
               class="flex items-center p-2 rounded-full font-semibold text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150 <?php if(Route::is('profile.show')): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                <span class="ml-8">View Public Profile</span>
            </a>

            <!-- Sub-link 2: Edit Settings -->
            <a href="<?php echo e(route('profile.edit')); ?>"
               class="flex items-center p-2 rounded-full font-semibold text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-150 <?php if(Route::is('profile.edit')): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                <span class="ml-8">Edit Settings</span>
            </a>

        </div>
    </div>
    <!-- END COLLAPSIBLE PROFILE MENU -->

    <!-- Discover (Future Feature - Placeholder) -->
    <a href="#" class="<?php echo e($baseClasses); ?>">
        <img src="<?php echo e(asset('icons/search.png')); ?>" alt="Discover" class="w-6 h-6 mr-4">
        Discover
    </a>

    <!-- Settings Link -->
    <a href="<?php echo e(route('settings.index')); ?>"
       class="<?php echo e($baseClasses); ?> <?php if($isSettings): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
        <img src="<?php echo e(asset('icons/setting.png')); ?>" alt="Settings" class="w-6 h-6 mr-4">
        Settings
    </a>

</nav><?php /**PATH C:\laragon\www\musicsocial\resources\views/layouts/navigation-social.blade.php ENDPATH**/ ?>