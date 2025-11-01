<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => 'Home Feed']); ?>

    <div x-data="{ isMusicShareModalOpen: false }">

        <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block col-span-2">
                    <div class="sticky top-0 pt-4">
                        <?php
                            $isHome = Route::is('dashboard');
                            $isProfile = Route::is('profile.show') || Route::is('profile.edit') || Route::is('profile.*');
                            $isSettings = Route::is('settings.index');
                            $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                            $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                        ?>

                        <nav x-data="{ profileMenuOpen: <?php echo \Illuminate\Support\Js::from($isProfile)->toHtml() ?> }" class="space-y-2">

                            <a href="<?php echo e(route('dashboard')); ?>"
                               class="<?php echo e($baseClasses); ?> <?php if($isHome): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                                <img src="<?php echo e(asset('icons/home.png')); ?>" alt="Home" class="w-6 h-6 mr-4">
                                Home
                            </a>

                            <div>
                                <button x-on:click="profileMenuOpen = !profileMenuOpen"
                                   class="<?php echo e($baseClasses); ?> w-full justify-start text-gray-800 <?php if($isProfile): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 mr-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.25a.75.75 0 0 1-.22-.515v-.315a6.666 6.666 0 0 1 2.78-4.757c.307-.22.682-.338 1.066-.338h6.148c.384 0 .759.118 1.066.338a6.666 6.666 0 0 1 2.78 4.757v.315c0 .325-.29.515-.514.515z" /></svg>
                                    Profile

                                    <svg class="ml-auto w-4 h-4 transform transition duration-200 text-gray-500"
                                         :class="{ 'rotate-180': profileMenuOpen }"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="profileMenuOpen" x-transition class="py-1 space-y-1">

                                    <a href="<?php echo e(route('profile.show', auth()->user()->name)); ?>"
                                       class="flex items-center p-2 rounded-full font-semibold text-sm hover:bg-gray-200 transition duration-150 <?php if(Route::is('profile.show')): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                                        <span class="ml-8">View Public Profile</span>
                                    </a>

                                    <a href="<?php echo e(route('profile.edit')); ?>"
                                       class="flex items-center p-2 rounded-full font-semibold text-sm hover:bg-gray-200 transition duration-150 <?php if(Route::is('profile.edit')): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                                        <span class="ml-8">Edit Settings</span>
                                    </a>

                                </div>
                            </div>
                            <a href="#" class="<?php echo e($baseClasses); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 mr-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                Discover
                            </a>

                            <!-- Settings Link -->
                            <a href="<?php echo e(route('settings.index')); ?>"
                               class="<?php echo e($baseClasses); ?> <?php if($isSettings): ?> <?php echo e($activeClasses); ?> <?php endif; ?>">
                                <img src="<?php echo e(asset('icons/setting.png')); ?>" alt="Settings" class="w-6 h-6 mr-4">
                                Settings
                            </a>
                        </nav>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-7">

                    <?php if (isset($component)) { $__componentOriginal2c3d6958985310adad7f89141edf5da6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c3d6958985310adad7f89141edf5da6 = $attributes; } ?>
<?php $component = App\View\Components\PostComposer::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('post-composer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PostComposer::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c3d6958985310adad7f89141edf5da6)): ?>
<?php $attributes = $__attributesOriginal2c3d6958985310adad7f89141edf5da6; ?>
<?php unset($__attributesOriginal2c3d6958985310adad7f89141edf5da6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c3d6958985310adad7f89141edf5da6)): ?>
<?php $component = $__componentOriginal2c3d6958985310adad7f89141edf5da6; ?>
<?php unset($__componentOriginal2c3d6958985310adad7f89141edf5da6); ?>
<?php endif; ?>

                    <div class="bg-white shadow-sm rounded-xl divide-y">
                        <?php $__currentLoopData = $shares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.share-card','data' => ['share' => $share]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('share-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['share' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($share)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a)): ?>
<?php $attributes = $__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a; ?>
<?php unset($__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a)): ?>
<?php $component = $__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a; ?>
<?php unset($__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mt-4">
                        <?php echo e($shares->links()); ?>

                    </div>

                </div>
                <div class="hidden md:block col-span-3">
                    <div class="sticky top-0 pt-4">
                        <?php echo $__env->make('layouts.sidebar-right', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div class="mt-4">
                            <button @click="isMusicShareModalOpen = true" class="bg-custom-mid-blue hover:bg-custom-dark-blue p-3 rounded-full shadow-lg transition w-full flex items-center justify-center">
                                <img src="<?php echo e(asset('icons/share.png')); ?>" alt="Share Music" class="w-8 h-8 mr-2">
                                <span class="text-white font-semibold">Share Music</span>
                            </button>
                        </div>
                    </div>
                </div>
                </div>
        </div>

        <?php if (isset($component)) { $__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.music-share-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('music-share-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be)): ?>
<?php $attributes = $__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be; ?>
<?php unset($__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be)): ?>
<?php $component = $__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be; ?>
<?php unset($__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be); ?>
<?php endif; ?>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\musicsocial\resources\views/dashboard.blade.php ENDPATH**/ ?>