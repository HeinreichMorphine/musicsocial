<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => ''.e($user->name).'\'s Profile']); ?>
    <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                    <?php
                        $isHome = Route::is('dashboard');
                        $isProfile = Route::is('profile.show') && (isset($user) && $user->id === auth()->id());
                        $isSettings = false;
                        $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                        $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                    ?>
                    <?php echo $__env->make('layouts.navigation-social', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="col-span-12 md:col-span-7">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            <?php echo e($user->name); ?>

                        </h2>

                        <div class="flex space-x-4">
                            <a href="<?php echo e(route('profile.followers', $user)); ?>" class="text-blue-500 hover:underline">Followers (<?php echo e($user->followers()->count()); ?>)</a>
                            <a href="<?php echo e(route('profile.following', $user)); ?>" class="text-blue-500 hover:underline">Following (<?php echo e($user->following()->count()); ?>)</a>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <?php $__empty_1 = true; $__currentLoopData = $user->shares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-600"><?php echo e($user->name); ?> has not shared anything yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    <?php echo $__env->make('layouts.sidebar-right', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
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
<?php endif; ?>
<?php /**PATH C:\laragon\www\musicsocial\resources\views/profile/show.blade.php ENDPATH**/ ?>